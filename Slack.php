<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Slack;

use Piwik\Log\LoggerInterface;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugins\ScheduledReports\ScheduledReports;
use Piwik\View;
use Piwik\Container\StaticContainer;
use Piwik\ReportRenderer;
use Piwik\Plugins\ScheduledReports\API as APIScheduledReports;

class Slack extends Plugin
{
    public const SLACK_CHANNEL_ID_PARAMETER = 'slackChannelID';
    public const SLACK_TYPE = 'slack';
    public const EVOLUTION_GRAPH_PARAMETER = 'evolutionGraph';
    public const DISPLAY_FORMAT_PARAMETER = 'displayFormat';
    public const DISPLAY_FORMAT_GRAPHS_ONLY = 2; // Display Graphs Only for all reports
    public const DISPLAY_FORMAT_TABLES_AND_GRAPHS = 3; // Display Tables and Graphs for all reports
    public const DISPLAY_FORMAT_TABLES_ONLY = 4; // Display only tables for all reports

    private static $availableParameters = array(
        self::SLACK_CHANNEL_ID_PARAMETER    => true,
    );

    private static $managedReportTypes = array(
        self::SLACK_TYPE => 'plugins/Slack/images/slack.png'
    );

    private static $managedReportFormats = array(
        ReportRenderer::PDF_FORMAT  => 'plugins/Morpheus/icons/dist/plugins/pdf.png',
        ReportRenderer::CSV_FORMAT  => 'plugins/Morpheus/images/export.png',
        ReportRenderer::TSV_FORMAT  => 'plugins/Morpheus/images/export.png',
    );

    public function registerEvents()
    {
        return [
            'ScheduledReports.getReportParameters'      => 'getReportParameters',
            'ScheduledReports.validateReportParameters' => 'validateReportParameters',
            'ScheduledReports.getReportMetadata'        => 'getReportMetadata',
            'ScheduledReports.getReportTypes'           => 'getReportTypes',
            'ScheduledReports.getReportFormats'         => 'getReportFormats',
            'ScheduledReports.getRendererInstance'      => 'getRendererInstance',
            'ScheduledReports.getReportRecipients'      => 'getReportRecipients',
            'ScheduledReports.processReports'           => 'processReports',
            'ScheduledReports.allowMultipleReports'     => 'allowMultipleReports',
            'ScheduledReports.sendReport'               => 'sendReport',
            'Template.reportParametersScheduledReports' => 'templateReportParametersScheduledReports',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
        ];
    }

    public function requiresInternetConnection()
    {
        return true;
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'Slack_ChannelID';
        $translationKeys[] = 'Slack_NoOauthTokenAdded';
        $translationKeys[] = 'Slack_SlackChannel';
    }

    public function validateReportParameters(&$parameters, $reportType)
    {
        if (self::manageEvent($reportType)) {
            $settings = StaticContainer::get(SystemSettings::class);
            if (empty($settings->slackOauthToken->getValue())) {
                throw new \Exception(Piwik::translate('Slack_OauthTokenRequiredErrorMessage'));
            } elseif (empty($parameters[self::SLACK_CHANNEL_ID_PARAMETER])) {
                throw new \Exception(Piwik::translate('Slack_SlackChannelIDRequiredErrorMessage'));
            }
        }
    }

    public function getReportMetadata(&$reportMetadata, $reportType, $idSite)
    {
        if (! self::manageEvent($reportType)) {
            return;
        }

        $availableReportMetadata = \Piwik\Plugins\API\API::getInstance()->getReportMetadata($idSite);

        $filteredReportMetadata = array();
        foreach ($availableReportMetadata as $reportMetadata) {
            // removing reports from the API category and MultiSites.getOne
            if (
                $reportMetadata['category'] == 'API' ||
                $reportMetadata['category'] == Piwik::translate('General_MultiSitesSummary') && $reportMetadata['name'] == Piwik::translate('General_SingleWebsitesDashboard')
            ) {
                continue;
            }

            $filteredReportMetadata[] = $reportMetadata;
        }

        $reportMetadata = $filteredReportMetadata;
    }

    public function getReportTypes(&$reportTypes)
    {
        $reportTypes = array_merge($reportTypes, self::$managedReportTypes);
    }

    public function getReportFormats(&$reportFormats, $reportType)
    {
        if (self::manageEvent($reportType)) {
            $reportFormats = array_merge($reportFormats, self::$managedReportFormats);
        }
    }

    public function getReportParameters(&$availableParameters, $reportType)
    {
        if (self::manageEvent($reportType)) {
            $availableParameters = self::$availableParameters;
        }
    }

    public function processReports(&$processedReports, $reportType, $outputType, $report)
    {
        if (! self::manageEvent($reportType)) {
            return;
        }

        $displayFormat = $report['parameters'][self::DISPLAY_FORMAT_PARAMETER];
        $evolutionGraph = $report['parameters'][self::EVOLUTION_GRAPH_PARAMETER];

        foreach ($processedReports as &$processedReport) {
            $metadata = $processedReport['metadata'];

            $isAggregateReport = !empty($metadata['dimension']);

            $processedReport['displayTable'] = $displayFormat != self::DISPLAY_FORMAT_GRAPHS_ONLY;

            $processedReport['displayGraph'] =
                ($isAggregateReport ?
                    $displayFormat == self::DISPLAY_FORMAT_GRAPHS_ONLY || $displayFormat == self::DISPLAY_FORMAT_TABLES_AND_GRAPHS
                    :
                    $displayFormat != self::DISPLAY_FORMAT_TABLES_ONLY)
                && \Piwik\SettingsServer::isGdExtensionEnabled()
                && \Piwik\Plugin\Manager::getInstance()->isPluginActivated('ImageGraph')
                && !empty($metadata['imageGraphUrl']);

            $processedReport['evolutionGraph'] = $evolutionGraph;

            // remove evolution metrics from MultiSites.getAll
            if ($metadata['module'] == 'MultiSites') {
                $columns = $processedReport['columns'];

                foreach (\Piwik\Plugins\MultiSites\API::getApiMetrics($enhanced = true) as $metricSettings) {
                    unset($columns[$metricSettings[\Piwik\Plugins\MultiSites\API::METRIC_EVOLUTION_COL_NAME_KEY]]);
                }

                $processedReport['metadata'] = $metadata;
                $processedReport['columns'] = $columns;
            }
        }
    }

    public function getRendererInstance(&$reportRenderer, $reportType, $outputType, $report)
    {
        if (! self::manageEvent($reportType)) {
            return;
        }

        $reportFormat = $report['format'];

        $reportRenderer = ReportRenderer::factory($reportFormat);
    }

    public function allowMultipleReports(&$allowMultipleReports, $reportType)
    {
        if (self::manageEvent($reportType)) {
            $allowMultipleReports = true;
        }
    }

    public function getReportRecipients(&$recipients, $reportType, $report)
    {
        if (!self::manageEvent($reportType) || empty($report['parameters'][self::SLACK_CHANNEL_ID_PARAMETER])) {
            return;
        }

        $recipients = [Piwik::translate('Slack_SlackChannel') . ': ' . $report['parameters'][self::SLACK_CHANNEL_ID_PARAMETER]];
    }

    /**
     * @param $reportType
     * @param $report
     * @param $contents
     * @param $filename
     * @param $prettyDate
     * @param $reportSubject
     * @param $reportTitle
     * @param $additionalFiles
     * @param Period|null $period
     * @param $force
     */
    public function sendReport(
        $reportType,
        $report,
        $contents,
        $filename,
        $prettyDate,
        $reportSubject,
        $reportTitle,
        $additionalFiles,
        $period,
        $force
    ) {
        if (! self::manageEvent($reportType)) {
            return;
        }
        $logger = StaticContainer::get(LoggerInterface::class);
        // Safeguard against sending the same report twice to the same email (unless $force is true)
        if (!$force && $this->reportAlreadySent($report, $period)) {
            $logger->warning(
                'Preventing the same scheduled report from being sent again (report #%s for period "%s")',
                $report['idreport'],
                $prettyDate
            );
            return;
        }

        $settings = StaticContainer::get(SystemSettings::class);
        $token = $settings->slackOauthToken->getValue();
        if (empty($token)) {
            $logger->error('Slack OAuth token not set');
            return;
        }

        $periods = ScheduledReports::getPeriodToFrequency();
        $scheduleReportSlack = new ScheduleReportSlack();
        $subject = Piwik::translate('Slack_PleaseFindYourReport', [$periods[$report['period']], $reportSubject]);
        $scheduleReportSlack->send($subject, $filename, $contents, $report['parameters'][self::SLACK_CHANNEL_ID_PARAMETER], $token);
    }

    public function templateReportParametersScheduledReports(&$out, $context = '')
    {
        if (Piwik::isUserIsAnonymous()) {
            return;
        }

        $view = new View('@Slack/reportParametersScheduledReports');
        $view->reportType = self::SLACK_TYPE;
        $view->context = $context;

        $settings = StaticContainer::get(SystemSettings::class);
        $view->isSlackOauthTokenAdded = !empty($settings->slackOauthToken->getValue());
        $out .= $view->render();
    }

    private static function manageEvent($reportType)
    {
        return in_array($reportType, array_keys(self::$managedReportTypes));
    }

    public function install()
    {
    }

    public function deactivate()
    {
        // delete all mobile reports
        $APIScheduledReports = APIScheduledReports::getInstance();
        $reports = $APIScheduledReports->getReports();

        foreach ($reports as $report) {
            if ($report['type'] == self::SLACK_TYPE) {
                $APIScheduledReports->deleteReport($report['idreport']);
            }
        }
    }

    public function uninstall()
    {
        // currently the UI does not allow to delete a plugin
        // when it becomes available, all the MobileMessaging settings (API credentials, phone numbers, etc..) should be removed from the option table
        return;
    }
}
