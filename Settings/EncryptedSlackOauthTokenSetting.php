<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Slack\Settings;

use Piwik\Log;
use Piwik\Plugins\Slack\Encryption;
use Piwik\Plugins\Slack\Exceptions\SecretConfigurationException;
use Piwik\Settings\Plugin\SystemSetting;
use Piwik\Settings\Storage\Backend\PluginSettingsTable;

class EncryptedSlackOauthTokenSetting extends SystemSetting
{
    /**
     * @var Encryption
     */
    private $encryption;

    public function __construct($name, $defaultValue, $type, $pluginName, ?Encryption $encryption = null)
    {
        parent::__construct($name, $defaultValue, $type, $pluginName);
        $this->encryption = $encryption ?: new Encryption();
    }

    public function getValue()
    {
        $value = parent::getValue();

        if (!is_string($value) || $value === '') {
            return $value;
        }

        try {
            return $this->encryption->decryptString($value);
        } catch (SecretConfigurationException $e) {
            Log::error('[Slack] ' . $e->getMessage());
            return $this->getDefaultValue();
        }
    }

    public function setValue($value)
    {
        if (($value === '' || !is_string($value)) && $this->hasUndecryptableValue()) {
            return;
        }

        parent::setValue($value);
        $normalizedValue = parent::getValue();

        if (!is_string($normalizedValue) || $normalizedValue === '' || $this->encryption->isEncrypted($normalizedValue)) {
            return;
        }

        $encryptedValue = $this->encryption->encryptString($normalizedValue);
        $this->storage->setValue($this->name, $encryptedValue);
        $backend = $this->storage->getBackend();
        if ($backend instanceof PluginSettingsTable && method_exists($backend, 'saveValue')) {
            $backend->saveValue($this->name, $encryptedValue);
            return;
        }

        $this->storage->save();
    }

    private function hasUndecryptableValue(): bool
    {
        $stored = parent::getValue();

        if (!is_string($stored) || !$this->encryption->isEncrypted($stored)) {
            return false;
        }

        try {
            $this->encryption->decryptString($stored);
            return false;
        } catch (SecretConfigurationException $e) {
            return true;
        }
    }
}
