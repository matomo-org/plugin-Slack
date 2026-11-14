<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Slack\tests;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugins\ScheduledReports\API;
use Piwik\Plugins\ScheduledReports\ScheduledReports;
use Piwik\Plugins\Slack\Slack;
use Piwik\Plugins\Slack\SystemSettings;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Version;

/**
 * @group Slack
 * @group SlackTest
 * @group Plugins
 */
class ScheduledReportsTest extends IntegrationTestCase
{
    /**
     * @var ScheduledReports
     */
    private $reports;
    private $reportIds = array();

    public function setUp(): void
    {
        parent::setUp();

        $this->reports = new ScheduledReports();
        $this->setIdentity('userlogin');

        \Piwik\Plugin\Manager::getInstance()->loadPlugins(array('ScheduledReports', 'Slack'));
        \Piwik\Plugin\Manager::getInstance()->installLoadedPlugins();

        for ($i = 1; $i <= 4; $i++) {
            Fixture::createWebsite('2014-01-01 00:00:00');
        }
    }

    public function testAddReportShouldFailWhenTokenNotSet()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Slack_OauthTokenRequiredErrorMessage');

        $this->addReport('userlogin', 1);
    }

    public function testAddReportSuccess()
    {
        $settings = StaticContainer::get(SystemSettings::class);
        $settings->slackOauthToken->setValue('test123');

        $this->addReport($userLogin = 'userlogin', $idSite = 1);
        $this->assertHasReport($userLogin, $idSite);
    }

    public function testAddReportSuccessCsv()
    {
        $settings = StaticContainer::get(SystemSettings::class);
        $settings->slackOauthToken->setValue('test123');

        $this->addReport($userLogin = 'userlogin', $idSite = 2, 'csv');
        $this->assertHasReport($userLogin, $idSite);
    }

    public function testAddReportSuccessTsv()
    {
        $settings = StaticContainer::get(SystemSettings::class);
        $settings->slackOauthToken->setValue('test123');

        $this->addReport($userLogin = 'userlogin', $idSite = 3, 'tsv');
        $this->assertHasReport($userLogin, $idSite);
    }

    public function testAddReportFailureWhenInvalidReportType()
    {
        $settings = StaticContainer::get(SystemSettings::class);
        $settings->slackOauthToken->setValue('test123');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('General_ExceptionInvalidReportRendererFormat');

        $this->addReport($userLogin = 'userlogin', $idSite = 3, 'html');
    }

    public function testGetAlertMessageEscapesInsertedValues()
    {
        $slack = StaticContainer::get(Slack::class);
        Fixture::loadAllTranslations();
        $alert = ['name' => 'A & B <x>', 'siteName' => 'Site &lt;one&gt; &amp; two', 'metric_condition' => 'less_than', 'metric_matched' => '5', 'value_new' => '1', 'value_old' => '2', 'idsite' => 1];
        $this->assertSame(
            'A &amp; B &lt;x&gt; has been triggered for website <http://localhost/tests/PHPUnit/proxy/index.php?idSite=1|Site &lt;one&gt; &amp; two> as the metric Visits &lt;total&gt; in report Visits &amp; Summary is 1 which is less than 5.',
            $slack->getAlertMessage($alert, 'Visits <total>', 'Visits & Summary')
        );
    }

    public function testSendReportEscapesReportSubject()
    {
        $settings = StaticContainer::get(SystemSettings::class);
        $settings->slackOauthToken->setValue('test123');
        Fixture::loadAllTranslations();

        $contents = 'report contents';
        $uploadUrl = 'https://files.slack.com/upload/v1/test';
        $initialComment = null;
        Piwik::addAction('Http.sendHttpRequest', function ($url, $params, &$response, &$status, &$headers) use ($contents, $uploadUrl, &$initialComment) {
            if (strpos($url, 'files.getUploadURLExternal') !== false) {
                $response = json_encode(['ok' => true, 'upload_url' => $uploadUrl, 'file_id' => 'F123']);
            } elseif ($url === $uploadUrl) {
                $response = 'OK - ' . strlen($contents);
            } elseif (strpos($url, 'files.completeUploadExternal') !== false) {
                $initialComment = $params['body']['initial_comment'];
                $response = json_encode(['ok' => true]);
            }
        });

        $report = [
            'idreport' => 1,
            'period' => 'day',
            'parameters' => [Slack::SLACK_CHANNEL_ID_PARAMETER => 'test123'],
        ];
        StaticContainer::get(Slack::class)->sendReport('slack', $report, $contents, 'report.pdf', '2024-01-01', 'x <!channel> & <https://evil.example|y>', 'title', [], null, true);

        $this->assertNotNull($initialComment);
        $this->assertStringContainsString('&lt;!channel&gt;', $initialComment);
        $this->assertStringContainsString('&amp;', $initialComment);
        $this->assertStringNotContainsString('<!channel>', $initialComment);
        $this->assertStringNotContainsString('<https://evil.example', $initialComment);
    }

    /**
     * @dataProvider getAlertData
     */
    public function testGetAlertMessage($alert, $expectedMessage)
    {
        $slack = StaticContainer::get(Slack::class);
        Fixture::loadAllTranslations();
        $this->assertSame($expectedMessage, $slack->getAlertMessage($alert, 'Unique Visitors', 'Visits Summary'));
    }

    public function getAlertData()
    {
        return [
            [['name' => 'Alert1', 'siteName' => 'test.com', 'metric_condition' => 'less_than', 'metric_matched' => '5', 'value_new' => '1', 'value_old' => '2', 'idsite' => 1], 'Alert1 has been triggered for website <http://localhost/tests/PHPUnit/proxy/index.php?idSite=1|test.com> as the metric Unique Visitors in report Visits Summary is 1 which is less than 5.'],
            [['name' => 'Alert2', 'siteName' => 'test.com', 'metric_condition' => 'greater_than', 'metric_matched' => '8', 'value_new' => '10', 'value_old' => '2', 'idsite' => 1], 'Alert2 has been triggered for website <http://localhost/tests/PHPUnit/proxy/index.php?idSite=1|test.com> as the metric Unique Visitors in report Visits Summary is 10 which is greater than 8.'],
            [['name' => 'Alert3', 'siteName' => 'test.com', 'metric_condition' => 'decrease_more_than', 'metric_matched' => '5', 'value_new' => '8', 'value_old' => '15', 'idsite' => 1], 'Alert3 has been triggered for website <http://localhost/tests/PHPUnit/proxy/index.php?idSite=1|test.com> as the metric Unique Visitors in report Visits Summary decreased more than 5 from 15 to 8.'],
            [['name' => 'Alert4', 'siteName' => 'test.com', 'metric_condition' => 'increase_more_than', 'metric_matched' => '5', 'value_new' => '30', 'value_old' => '20', 'idsite' => 1], 'Alert4 has been triggered for website <http://localhost/tests/PHPUnit/proxy/index.php?idSite=1|test.com> as the metric Unique Visitors in report Visits Summary increased more than 5 from 20 to 30.'],
            [['name' => 'Alert5', 'siteName' => 'test.com', 'metric_condition' => 'percentage_decrease_more_than', 'metric_matched' => '5', 'value_new' => '8', 'value_old' => '15', 'idsite' => 1], 'Alert5 has been triggered for website <http://localhost/tests/PHPUnit/proxy/index.php?idSite=1|test.com> as the metric Unique Visitors in report Visits Summary decreased more than 5% from 15 to 8.'],
            [['name' => 'Alert6', 'siteName' => 'test.com', 'metric_condition' => 'percentage_increase_more_than', 'metric_matched' => '5', 'value_new' => '30', 'value_old' => '20', 'idsite' => 1], 'Alert6 has been triggered for website <http://localhost/tests/PHPUnit/proxy/index.php?idSite=1|test.com> as the metric Unique Visitors in report Visits Summary increased more than 5% from 20 to 30.'],
        ];
    }

    public function testValidateReportParametersSlackTokenNotSetShouldThrowOauthException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Slack_OauthTokenRequiredErrorMessage');
        $parameters = [ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_GRAPHS_ONLY];
        Piwik::postEvent('ScheduledReports.validateReportParameters', [&$parameters, 'slack']);
    }

    public function testValidateReportParametersInvalidDisplayFormatAnswers400()
    {
        $this->expectException(\Piwik\Http\BadRequestException::class);
        $this->expectExceptionCode($this->getExpectedClientErrorCode());
        $parameters = [ScheduledReports::DISPLAY_FORMAT_PARAMETER => 'invalidFormat'];
        Piwik::postEvent('ScheduledReports.validateReportParameters', [&$parameters, 'slack']);
    }

    public function testValidateReportParametersSlackTokenNotSetShouldThrowSlackChannelIdEmptyException()
    {
        $settings = StaticContainer::get(SystemSettings::class);
        $settings->slackOauthToken->setValue('test123');
        $this->expectException(\Piwik\Http\BadRequestException::class);
        $this->expectExceptionCode($this->getExpectedClientErrorCode());
        $this->expectExceptionMessage('Slack_SlackChannelIdRequiredErrorMessage');
        $parameters = [ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_GRAPHS_ONLY];
        Piwik::postEvent('ScheduledReports.validateReportParameters', [&$parameters, 'slack']);
    }

    public function testValidateReportParametersSlackTokenNotSetShouldThrowSlackChannelIdEmptyException2()
    {
        $settings = StaticContainer::get(SystemSettings::class);
        $settings->slackOauthToken->setValue('test123');
        $this->expectException(\Piwik\Http\BadRequestException::class);
        $this->expectExceptionCode($this->getExpectedClientErrorCode());
        $this->expectExceptionMessage('Slack_SlackChannelIdRequiredErrorMessage');
        $parameters = [ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_GRAPHS_ONLY, Slack::SLACK_CHANNEL_ID_PARAMETER => ''];
        Piwik::postEvent('ScheduledReports.validateReportParameters', [&$parameters, 'slack']);
    }

    public function testValidateReportParametersSlackTokenNotSetShouldThrowSlackChannelIdInvalidException()
    {
        $settings = StaticContainer::get(SystemSettings::class);
        $settings->slackOauthToken->setValue('test123');
        $this->expectException(\Piwik\Http\BadRequestException::class);
        $this->expectExceptionCode($this->getExpectedClientErrorCode());
        $this->expectExceptionMessage('Slack_SlackChannelIdInvalidErrorMessage');
        $parameters = [ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_GRAPHS_ONLY, Slack::SLACK_CHANNEL_ID_PARAMETER => 'test123@11'];
        Piwik::postEvent('ScheduledReports.validateReportParameters', [&$parameters, 'slack']);
    }

    public function testValidateReportParametersSlackTokenNotSetShouldThrowSlackChannelIdInvalidException2()
    {
        $settings = StaticContainer::get(SystemSettings::class);
        $settings->slackOauthToken->setValue('test123');
        $this->expectException(\Piwik\Http\BadRequestException::class);
        $this->expectExceptionCode($this->getExpectedClientErrorCode());
        $this->expectExceptionMessage('Slack_SlackChannelIdInvalidErrorMessage');
        $parameters = [ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_GRAPHS_ONLY, Slack::SLACK_CHANNEL_ID_PARAMETER => 'test123,Demo@11'];
        Piwik::postEvent('ScheduledReports.validateReportParameters', [&$parameters, 'slack']);
    }

    public function testValidateReportParametersSlackTokenShouldNotThrowAnyException()
    {
        $settings = StaticContainer::get(SystemSettings::class);
        $settings->slackOauthToken->setValue('test123');
        $parameters = [ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_GRAPHS_ONLY, Slack::SLACK_CHANNEL_ID_PARAMETER => 'test123'];
        Piwik::postEvent('ScheduledReports.validateReportParameters', [&$parameters, 'slack']);
        $this->assertNotEmpty($parameters);
    }

    public function testValidateReportParametersSlackTokenShouldNotThrowAnyException2()
    {
        $settings = StaticContainer::get(SystemSettings::class);
        $settings->slackOauthToken->setValue('test123');
        $parameters = [ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_GRAPHS_ONLY, Slack::SLACK_CHANNEL_ID_PARAMETER => 'test123,dEmoA,abcd123'];
        Piwik::postEvent('ScheduledReports.validateReportParameters', [&$parameters, 'slack']);
        $this->assertNotEmpty($parameters);
    }

    private function assertHasReport($login, $idSite)
    {
        $report = $this->getReport($login, $idSite);

        $this->assertNotEmpty($report, "Report for $login, $idSite should exist but does not");
    }

    private function getReport($login, $idSite)
    {
        $this->setIdentity($login);

        return API::getInstance()->getReports($idSite, 'day', $this->reportIds[$login . '_' . $idSite]);
    }

    private function addReport($login, $idSite, $reportFormat = 'pdf')
    {
        $this->setIdentity($login);

        $reportType   = 'slack';
        $reports      = array();
        $parameters   = array(
            Slack::SLACK_CHANNEL_ID_PARAMETER => 'test123',
            ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DEFAULT_DISPLAY_FORMAT,
            ScheduledReports::EVOLUTION_GRAPH_PARAMETER => ScheduledReports::EVOLUTION_GRAPH_PARAMETER_DEFAULT_VALUE,
        );

        $reportId = API::getInstance()->addReport($idSite, 'description', 'day', 3, $reportType, $reportFormat, $reports, $parameters);
        $this->reportIds[$login . '_' . $idSite] = $reportId;
    }

    private function setIdentity($login)
    {
        FakeAccess::$identity  = $login;
        FakeAccess::$superUser = true;
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }

    private function getExpectedClientErrorCode(): int
    {
        return version_compare(Version::VERSION, '6.1.0-alpha', '>=') ? 400 : 0;
    }
}
