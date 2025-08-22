<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Slack\tests;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\ScheduledReports\API;
use Piwik\Plugins\ScheduledReports\ScheduledReports;
use Piwik\Plugins\Slack\Slack;
use Piwik\Plugins\Slack\SystemSettings;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

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
        $parameters   = array(Slack::SLACK_CHANNEL_ID_PARAMETER => 'test123');

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
}
