<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Slack\tests;

use Piwik\Common;
use Piwik\Config;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugins\CoreAdminHome\EncryptionKeyRotator;
use Piwik\Plugins\CoreAdminHome\tests\Framework\Mock\FileBackedConfig;
use Piwik\Plugins\Slack\Configuration;
use Piwik\Plugins\Slack\Encryption;
use Piwik\Plugins\Slack\Exceptions\SecretConfigurationException;
use Piwik\Plugins\Slack\SystemSettings;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Slack
 * @group EncryptionKeyRotationTest
 * @group Plugins
 */
class EncryptionKeyRotationTest extends IntegrationTestCase
{
    private $backupSlackConfig = [];

    /**
     * @var FileBackedConfig|null
     */
    private $config;

    public function setUp(): void
    {
        parent::setUp();

        $this->backupSlackConfig = Config::getInstance()->Slack ?: [];

        // the rotation command is only available from the Matomo release that introduced it
        if (!class_exists(EncryptionKeyRotator::class)) {
            $this->markTestSkipped('Encryption key rotation is not available in this Matomo version.');
        }

        Config::getInstance()->Slack = [Configuration::KEY_ENCRYPTION_KEY => 'old-key'];
        $this->config = FileBackedConfig::replaceTestConfig();

        Fixture::loadAllTranslations();
        Fixture::createSuperUser();
    }

    public function tearDown(): void
    {
        if ($this->config !== null) {
            $this->config->deleteFile();
        }

        Config::getInstance()->Slack = $this->backupSlackConfig;

        parent::tearDown();
    }

    public function testRotationReEncryptsTheOauthTokenWithTheNewKey()
    {
        $settings = new SystemSettings();
        $settings->slackOauthToken->setValue('xoxb-token');
        $settings->save();
        $oldStoredValue = $this->getStoredTokenValue();

        $targets = [];
        Piwik::postEvent('CoreAdminHome.getEncryptionKeyRotationTargets', [&$targets]);
        $count = (new EncryptionKeyRotator())->rotate('Slack', $targets['Slack']);

        $this->assertSame(1, $count);

        $newKey = Config::getInstance()->Slack[Configuration::KEY_ENCRYPTION_KEY];
        $this->assertSame($newKey, $this->config->readFile()['Slack'][Configuration::KEY_ENCRYPTION_KEY]);
        $this->assertNotSame('old-key', $newKey);

        $newStoredValue = $this->getStoredTokenValue();
        $this->assertNotSame($oldStoredValue, $newStoredValue);
        $this->assertSame('xoxb-token', Encryption::withKey($newKey)->decryptString($newStoredValue));
        $this->assertSame('xoxb-token', (new SystemSettings())->slackOauthToken->getValue());

        $this->expectException(SecretConfigurationException::class);
        Encryption::withKey('old-key')->decryptString($newStoredValue);
    }

    private function getStoredTokenValue(): string
    {
        return Db::fetchOne(
            'SELECT `setting_value` FROM `' . Common::prefixTable('plugin_setting') . '` WHERE `plugin_name` = ? AND `user_login` = ? AND `setting_name` = ?',
            ['Slack', '', 'slackOauthToken']
        );
    }
}
