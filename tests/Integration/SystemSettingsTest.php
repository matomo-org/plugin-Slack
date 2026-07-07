<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Slack\tests;

use Piwik\Config;
use Piwik\Plugins\Slack\Configuration;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\Slack\SystemSettings;
use Piwik\Tests\Framework\Fixture;
use Piwik\Settings\Storage\Factory;

/**
 * @group Slack
 * @group SystemSettingsTest
 * @group Plugins
 */

class SystemSettingsTest extends IntegrationTestCase
{
    private $settings;
    private $backupSlackConfig = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->backupSlackConfig = Config::getInstance()->Slack ?: [];

        Fixture::loadAllTranslations();

        Fixture::createSuperUser();
        Fixture::createWebsite('2014-01-01 00:01:02');

        $this->settings = new SystemSettings();
    }

    public function tearDown(): void
    {
        Config::getInstance()->Slack = $this->backupSlackConfig;
        Config::getInstance()->forceSave();

        parent::tearDown();
    }

    public function testSlackOauthTokenDefaultValue()
    {
        $this->assertEmpty($this->settings->slackOauthToken->getValue());
    }

    public function testSlackOauthTokenValueChangeSuccess()
    {
        $this->settings->slackOauthToken->setValue('token');
        $this->assertEquals('token', $this->settings->slackOauthToken->getValue());
        $this->assertStoredValueIsEncrypted('token');
    }

    public function testSlackOauthTokenValueChangeSuccess2()
    {
        $this->settings->slackOauthToken->setValue('token ');
        $this->assertEquals('token', $this->settings->slackOauthToken->getValue());
        $this->assertStoredValueIsEncrypted('token');
    }

    public function testShouldNotOverwriteEncryptedValueWhenKeyIsInvalidAndBlankValueIsSaved()
    {
        $this->settings->slackOauthToken->setValue('token');
        $storedValue = $this->getStoredTokenValue();

        Config::getInstance()->Slack[Configuration::KEY_ENCRYPTION_KEY] = 'invalid-key';

        $this->settings = new SystemSettings();

        $this->assertSame('', $this->settings->slackOauthToken->getValue());

        $this->settings->slackOauthToken->setValue('');

        $this->assertSame($storedValue, $this->getStoredTokenValue());
    }

    private function assertStoredValueIsEncrypted(string $expectedPlaintext): void
    {
        $storedValue = $this->getStoredTokenValue();

        $this->assertNotSame($expectedPlaintext, $storedValue);
        $this->assertStringStartsWith('enc:v1:', $storedValue);
    }

    private function getStoredTokenValue(): string
    {
        $backend = (new Factory())->getPluginStorage('Slack', '')->getBackend();

        return (string) $backend->loadValue('slackOauthToken', '');
    }
}
