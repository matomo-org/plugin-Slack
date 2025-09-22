<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Slack\tests;

use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tests\Framework\TestingEnvironmentManipulator;
use Piwik\Plugins\CustomAlerts\API;

/**
 * @group Slack
 * @group CustomAlertsApiTest
 * @group Plugins
 */
class CustomAlertsApiTest extends IntegrationTestCase
{
    /**
     * @var \Piwik\Plugins\CustomAlerts\API
     */
    protected $api;

    protected $idSite;

    public function setUp(): void
    {
        self::$fixture->extraPluginsToLoad = array('CustomAlerts');
        TestingEnvironmentManipulator::$extraPluginsToLoad = self::$fixture->extraPluginsToLoad;

        parent::setUp();

        $pluginManager = \Piwik\Plugin\Manager::getInstance();
        $pluginManager->loadPlugin('CustomAlerts');
        $pluginManager->installLoadedPlugins();
        $pluginManager->activatePlugin('CustomAlerts');
        $this->api = API::getInstance();
        $this->idSite = Fixture::createWebsite('2012-08-09 11:22:33');
    }

    public function testAddAlertShouldThrowExceptionIfEmptySlackChannelId()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Slack_SlackChannelIdRequiredErrorMessage');
        $this->addAlert();
    }

    public function testAddAlertSuccess()
    {
        $id = $this->addAlert($slackChannelId = 'channelID');
        $this->assertEquals(1, $id);
        $alert = $this->api->getAlert($id);
        $this->assertEquals(['email','slack'], $alert['report_mediums']);
        $this->assertEquals($slackChannelId, $alert['slack_channel_id']);
    }

    public function testUpdateAlertShouldThrowExceptionIfEmptySlackChannelId()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Slack_SlackChannelIdRequiredErrorMessage');
        $idAlert = $this->addAlert('channelID');
        $this->updateAlert($idAlert);
    }

    public function testUpdateAlertSuccess()
    {
        $idAlert = $this->addAlert('channelID');
        $this->updateAlert($idAlert, 'channelIDNew');
        $alert = $this->api->getAlert($idAlert);
        $this->assertEquals(['email','slack'], $alert['report_mediums']);
        $this->assertEquals('channelIDNew', $alert['slack_channel_id']);
    }

    private function addAlert($slackChannelId = '')
    {
        return $this->api->addAlert(
            'Test Slack and Email',
            $this->idSite,
            'day',
            1,
            [],
            [],
            'nb_visits',
            'less_than',
            $metricMatched = 5,
            1,
            'MultiSites_getOne',
            'matches_exactly',
            'Piwik',
            ['email', 'slack'],
            $slackChannelId
        );
    }

    private function updateAlert($idAlert, $slackChannelId = '')
    {
        return $this->api->editAlert(
            $idAlert,
            'Test Slack and Email',
            $this->idSite,
            'day',
            1,
            [],
            [],
            'nb_visits',
            'less_than',
            $metricMatched = 5,
            1,
            'MultiSites_getOne',
            'matches_exactly',
            'Piwik',
            ['email', 'slack'],
            $slackChannelId
        );
    }
}
