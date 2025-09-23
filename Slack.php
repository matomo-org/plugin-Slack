<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Slack;

use Piwik\Log\LoggerInterface;
use Piwik\Option;
use Piwik\Period;
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

    private static $availableParameters = array(
        self::SLACK_CHANNEL_ID_PARAMETER => true,
        ScheduledReports::EVOLUTION_GRAPH_PARAMETER => false,
        ScheduledReports::DISPLAY_FORMAT_PARAMETER => true,
    );

    private static $managedReportTypes = array(
        self::SLACK_TYPE => 'plugins/Slack/images/slack.png'
    );

    private static $managedReportFormats = array(
        ReportRenderer::PDF_FORMAT => 'plugins/Morpheus/icons/dist/plugins/pdf.png',
        ReportRenderer::CSV_FORMAT => 'plugins/Morpheus/images/export.png',
        ReportRenderer::TSV_FORMAT => 'plugins/Morpheus/images/export.png',
    );

    public function registerEvents()
    {
        return [
            'ScheduledReports.getReportParameters' => 'getReportParameters',
            'ScheduledReports.validateReportParameters' => 'validateReportParameters',
            'ScheduledReports.getReportMetadata' => 'getReportMetadata',
            'ScheduledReports.getReportTypes' => 'getReportTypes',
            'ScheduledReports.getReportFormats' => 'getReportFormats',
            'ScheduledReports.getRendererInstance' => 'getRendererInstance',
            'ScheduledReports.getReportRecipients' => 'getReportRecipients',
            'ScheduledReports.processReports' => 'processReports',
            'ScheduledReports.allowMultipleReports' => 'allowMultipleReports',
            'ScheduledReports.sendReport' => 'sendReport',
            'Template.reportParametersScheduledReports' => 'templateReportParametersScheduledReports',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'CustomAlerts.validateReportParameters' => 'validateCustomAlertReportParameters',
            'CustomAlerts.sendNewAlerts' => 'sendNewAlerts',
        ];
    }

    public function requiresInternetConnection()
    {
        return true;
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'Slack_ChannelId';
        $translationKeys[] = 'Slack_NoOauthTokenAdded';
        $translationKeys[] = 'Slack_SlackChannel';
        $translationKeys[] = 'Slack_SlackEnterYourSlackChannelIdHelpText';
    }

    /**
     *
     * Validates the Schedule Report for Slack reportType
     *
     * @param $parameters
     * @param $reportType
     * @return void
     * @throws \Piwik\Exception\DI\DependencyException
     * @throws \Piwik\Exception\DI\NotFoundException
     */
    public function validateReportParameters(&$parameters, $reportType)
    {
        if (!self::isSlackEvent($reportType)) {
            return;
        }

        $reportFormat = $parameters[ScheduledReports::DISPLAY_FORMAT_PARAMETER];
        $availableDisplayFormats = array_keys(ScheduledReports::getDisplayFormats());
        if (!in_array($reportFormat, $availableDisplayFormats)) {
            throw new \Exception(
                Piwik::translate(
                    'General_ExceptionInvalidAggregateReportsFormat',
                    array($reportFormat, implode(', ', $availableDisplayFormats))
                )
            );
        }

        // evolutionGraph is an optional parameter
        if (!isset($parameters[ScheduledReports::EVOLUTION_GRAPH_PARAMETER])) {
            $parameters[ScheduledReports::EVOLUTION_GRAPH_PARAMETER] = ScheduledReports::EVOLUTION_GRAPH_PARAMETER_DEFAULT_VALUE;
        } else {
            $parameters[ScheduledReports::EVOLUTION_GRAPH_PARAMETER] = self::valueIsTrue($parameters[ScheduledReports::EVOLUTION_GRAPH_PARAMETER]);
        }

        $settings = StaticContainer::get(SystemSettings::class);
        if (empty($settings->slackOauthToken->getValue())) {
            throw new \Exception(Piwik::translate('Slack_OauthTokenRequiredErrorMessage'));
        } elseif (empty($parameters[self::SLACK_CHANNEL_ID_PARAMETER])) {
            throw new \Exception(Piwik::translate('Slack_SlackChannelIdRequiredErrorMessage'));
        }
    }

    /**
     *
     * Get report metadata for Slack scheduled report
     *
     * @param $availableReportMetadata
     * @param $reportType
     * @param $idSite
     * @return void
     */
    public function getReportMetadata(&$availableReportMetadata, $reportType, $idSite)
    {
        if (!self::isSlackEvent($reportType)) {
            return;
        }

        // Use same metadata as E-mail report from ScheduledReports plugin
        Piwik::postEvent(
            'ScheduledReports.getReportMetadata',
            [&$availableReportMetadata, ScheduledReports::EMAIL_TYPE, $idSite]
        );
    }

    /**
     *
     * Adds Slack as a reportType in Schedule Reports
     *
     * @param $reportTypes
     * @return void
     */
    public function getReportTypes(&$reportTypes)
    {
        $reportTypes = array_merge($reportTypes, self::$managedReportTypes);
    }

    /**
     *
     * Adds allowed reportTypes for Slack, e.g. PDF, CSV and TSV
     *
     * @param $reportFormats
     * @param $reportType
     * @return void
     */
    public function getReportFormats(&$reportFormats, $reportType)
    {
        if (self::isSlackEvent($reportType)) {
            $reportFormats = array_merge($reportFormats, self::$managedReportFormats);
        }
    }

    /**
     *
     * Adds report parameter for Slack, e.g. SlackChannelID
     *
     * @param $availableParameters
     * @param $reportType
     * @return void
     */
    public function getReportParameters(&$availableParameters, $reportType)
    {
        if (self::isSlackEvent($reportType)) {
            $availableParameters = self::$availableParameters;
        }
    }

    /**
     *
     * Process the Schedule report for reportType Slack
     *
     * @param $processedReports
     * @param $reportType
     * @param $outputType
     * @param $report
     * @return void
     */
    public function processReports(&$processedReports, $reportType, $outputType, $report)
    {
        if (!self::isSlackEvent($reportType)) {
            return;
        }

        // Use same metadata as E-mail report from ScheduledReports plugin
        Piwik::postEvent(
            'ScheduledReports.processReports',
            [&$processedReports, ScheduledReports::EMAIL_TYPE, $outputType, $report]
        );
    }

    /**
     *
     * Sets the rendered instance based on reportFormat for Slack
     *
     * @param $reportRenderer
     * @param $reportType
     * @param $outputType
     * @param $report
     * @return void
     * @throws \Exception
     */
    public function getRendererInstance(&$reportRenderer, $reportType, $outputType, $report)
    {
        if (!self::isSlackEvent($reportType)) {
            return;
        }

        $reportFormat = $report['format'];

        $reportRenderer = ReportRenderer::factory($reportFormat);
    }

    /**
     *
     * To allow multiple reports in a single file
     *
     * @param $allowMultipleReports
     * @param $reportType
     * @return void
     */
    public function allowMultipleReports(&$allowMultipleReports, $reportType)
    {
        if (self::isSlackEvent($reportType)) {
            $allowMultipleReports = true;
        }
    }

    /**
     *
     * Displays the recipients in the list of Schedule Reports
     *
     * @param $recipients
     * @param $reportType
     * @param $report
     * @return void
     */
    public function getReportRecipients(&$recipients, $reportType, $report)
    {
        if (!self::isSlackEvent($reportType) || empty($report['parameters'][self::SLACK_CHANNEL_ID_PARAMETER])) {
            return;
        }

        $recipients = [Piwik::translate('Slack_SlackChannel') . ': ' . $report['parameters'][self::SLACK_CHANNEL_ID_PARAMETER]];
    }

    /**
     *
     * Code to send a Schedule Report via Slack
     *
     * @param $reportType
     * @param $report
     * @param $contents
     * @param $filename
     * @param $prettyDate
     * @param $reportSubject
     * @param $reportTitle
     * @param $additionalFiles
     * @param $period
     * @param $force
     * @return void
     * @throws \Piwik\Exception\DI\DependencyException
     * @throws \Piwik\Exception\DI\NotFoundException
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
        if (!self::isSlackEvent($reportType)) {
            return;
        }
        $logger = StaticContainer::get(LoggerInterface::class);
        // Safeguard against sending the same report twice to the same Slack channel (unless $force is true)
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
        $subject = Piwik::translate('Slack_PleaseFindYourReport', [$periods[$report['period']], $reportSubject]);
        $channelId = $report['parameters'][self::SLACK_CHANNEL_ID_PARAMETER];
        $scheduleReportSlack = new ScheduleReportSlack($subject, $filename, $contents, $channelId, $token);
        $scheduleReportSlack->send();
    }

    /**
     *
     * Add the view template for Slack report parameters
     *
     * @param $out
     * @param $context
     * @return void
     * @throws \Piwik\Exception\DI\DependencyException
     * @throws \Piwik\Exception\DI\NotFoundException
     */
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
        $view->defaultDisplayFormat = ScheduledReports::DEFAULT_DISPLAY_FORMAT;
        $view->defaultFormat = ReportRenderer::PDF_FORMAT;
        $view->defaultEvolutionGraph = ScheduledReports::EVOLUTION_GRAPH_PARAMETER_DEFAULT_VALUE;
        $out .= $view->render();
    }

    private static function isSlackEvent($reportType): bool
    {
        return in_array($reportType, array_keys(self::$managedReportTypes));
    }

    public function install()
    {
    }

    public function deactivate()
    {
        // delete all slack reports
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
        return;
    }

    /**
     *
     * Validation check for CustomAlert report parameters
     *
     * @param $parameters
     * @param $alertMedium
     * @return void
     * @throws \Exception
     */
    public function validateCustomAlertReportParameters($parameters, $alertMedium)
    {
        if ($alertMedium === self::SLACK_TYPE && empty($parameters[self::SLACK_CHANNEL_ID_PARAMETER])) {
            throw new \Exception(Piwik::translate('Slack_SlackChannelIdRequiredErrorMessage'));
        }
    }

    /**
     *
     * Code to send CustomAlerts via Slack
     *
     * @param $triggeredAlerts
     * @return void
     * @throws \Piwik\Exception\DI\DependencyException
     * @throws \Piwik\Exception\DI\NotFoundException
     */
    public function sendNewAlerts($triggeredAlerts): void
    {
        if (!empty($triggeredAlerts)) {
            $enrichTriggerAlerts = StaticContainer::get(EnrichTriggeredAlerts::class);
            $triggeredAlerts = $enrichTriggerAlerts->enrichTriggeredAlerts($triggeredAlerts);
            $settings = StaticContainer::get(SystemSettings::class);
            $token = $settings->slackOauthToken->getValue();
            if (empty($token)) {
                return;
            }
            $slackApi = new SlackApi($token);
            $groupedAlerts = $this->groupAlertsByChannelId($triggeredAlerts);
            foreach ($groupedAlerts as $slackChannelId => $alert) {
                if (!$slackApi->sendMessage(implode("\n", $alert['message']), $slackChannelId)) {
                    $logger = StaticContainer::get(LoggerInterface::class);
                    $logger->debug('Slack alert failed for following alerts: ' . implode("\n", $alert['name']));
                }
            }
        }
    }

    /**
     *
     * Group alerts by slackChannelID to reduce number of network calls for multiple alerts
     *
     * @param array $alerts
     * @return array
     */
    private function groupAlertsByChannelId(array $alerts): array
    {
        $groupedAlerts = [];
        foreach ($alerts as $alert) {
            if (!in_array(self::SLACK_TYPE, $alert['report_mediums']) || empty($alert['slack_channel_id'])) {
                continue;
            }
            $metric = !empty($alert['reportMetric']) ? $alert['reportMetric'] : $alert['metric'];
            $reportName = !empty($alert['reportName']) ? $alert['reportName'] : $alert['report'];
            $groupedAlerts[$alert['slack_channel_id']]['message'][] = $this->getAlertMessage($alert, $metric, $reportName);
            $groupedAlerts[$alert['slack_channel_id']]['name'][] = $alert['name'];
        }

        return $groupedAlerts;
    }

    /**
     *
     * Returns the alert message to send via Slack
     *
     * @param array $alert
     * @param string $metric
     * @param string $reportName
     * @return string
     */
    public function getAlertMessage(array $alert, string $metric, string $reportName): string
    {
        return Piwik::translate('Slack_SlackAlertContent', [$alert['name'], $alert['siteName'], $metric, $reportName, $this->transformAlertCondition($alert)]);
    }

    /**
     *
     * Transform the alert condition to text
     *
     * @param array $alert
     * @return string
     */
    private function transformAlertCondition(array $alert): string
    {
        switch ($alert['metric_condition']) {
            case 'less_than':
                return Piwik::translate('CustomAlerts_ValueIsLessThan', [$alert['metric_matched'], $alert['value_new']]);
            case 'greater_than':
                return Piwik::translate('CustomAlerts_ValueIsGreaterThan', [$alert['metric_matched'], $alert['value_new']]);
            case 'decrease_more_than':
                return Piwik::translate('CustomAlerts_ValueDecreasedMoreThan', [$alert['metric_matched'], $alert['value_old'] ?? '-', $alert['value_new']]);
            case 'increase_more_than':
                return Piwik::translate('CustomAlerts_ValueIncreasedMoreThan', [$alert['metric_matched'], $alert['value_old'] ?? '-', $alert['value_new']]);
            case 'percentage_decrease_more_than':
                return Piwik::translate('CustomAlerts_ValuePercentageDecreasedMoreThan', [$alert['metric_matched'], $alert['value_old'] ?? '-', $alert['value_new']]);
            case 'percentage_increase_more_than':
                return Piwik::translate('CustomAlerts_ValuePercentageIncreasedMoreThan', [$alert['metric_matched'], $alert['value_old'] ?? '-', $alert['value_new']]);
        }

        return '';
    }

    private function reportAlreadySent($report, Period $period)
    {
        $key = ScheduledReports::OPTION_KEY_LAST_SENT_DATERANGE . $report['idreport'];

        $previousDate = Option::get($key);

        return $previousDate === $period->getRangeString();
    }

    private static function valueIsTrue($value)
    {
        return $value == 'true' || $value == 1 || $value == '1' || $value === true;
    }
}
