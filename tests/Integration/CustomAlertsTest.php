<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Slack\tests;

use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\CustomAlerts\CustomAlerts;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tests\Framework\TestingEnvironmentManipulator;

/**
 * @group Slack
 * @group CustomAlertsTest
 * @group Plugins
 */

class CustomAlertsTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        self::$fixture->extraPluginsToLoad = array('CustomAlerts');
        TestingEnvironmentManipulator::$extraPluginsToLoad = self::$fixture->extraPluginsToLoad;

        parent::setUp();

        $pluginManager = \Piwik\Plugin\Manager::getInstance();
        $pluginManager->loadPlugin('CustomAlerts');
        $pluginManager->installLoadedPlugins();
        $pluginManager->activatePlugin('CustomAlerts');
    }
    public function testGetReportMediumOptions()
    {
        $this->assertEquals([
            ['key' => 'email', 'value' => 'CustomAlerts_MediumEmail', 'disabled' => false],
            ['key' => 'mobile', 'value' => 'CustomAlerts_MediumMobile', 'disabled' => false],
            ['key' => 'slack', 'value' => 'CustomAlerts_MediumSlack', 'disabled' => false],
        ], CustomAlerts::getReportMediumOptions());
    }
}
