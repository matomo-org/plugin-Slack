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

/**
 * @group Slack
 * @group CustomAlertsTest
 * @group Plugins
 */

class CustomAlertsTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        PluginManager::getInstance()->activatePlugin('CustomAlerts');
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
