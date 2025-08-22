<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Slack\tests;

use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\Slack\SystemSettings;
use Piwik\Tests\Framework\Fixture;

class SystemSettingsTest extends IntegrationTestCase
{
    private $settings;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::loadAllTranslations();

        Fixture::createSuperUser();
        Fixture::createWebsite('2014-01-01 00:01:02');

        $this->settings = new SystemSettings();
    }

    public function testSlackOauthTokenDefaultValue()
    {
        $this->assertEmpty($this->settings->slackOauthToken->getValue());
    }

    public function testSlackOauthTokenValueChangeSuccess()
    {
        $this->settings->slackOauthToken->setValue('token');
        $this->assertEquals('token', $this->settings->slackOauthToken->getValue());
    }

    public function testSlackOauthTokenValueChangeSuccess2()
    {
        $this->settings->slackOauthToken->setValue('token ');
        $this->assertEquals('token', $this->settings->slackOauthToken->getValue());
    }
}
