<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Slack;

use Piwik\Common;
use Piwik\Db;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;

class Updates_5_1_0 extends PiwikUpdates
{
    public function doUpdate(Updater $updater)
    {
        $configuration = new Configuration();
        $configuration->install();

        $encryption = new Encryption($configuration);
        $table = Common::prefixTable('plugin_setting');
        $rows = Db::fetchAll(
            'SELECT `setting_value` FROM ' . $table . ' WHERE `plugin_name` = ? AND `user_login` = ? AND `setting_name` = ?',
            ['Slack', '', 'slackOauthToken']
        );

        foreach ($rows as $row) {
            $value = $row['setting_value'] ?? '';

            if (!is_string($value) || $value === '' || $encryption->isEncrypted($value)) {
                continue;
            }

            Db::query(
                'UPDATE ' . $table . ' SET `setting_value` = ? WHERE `plugin_name` = ? AND `user_login` = ? AND `setting_name` = ? AND `setting_value` = ?',
                [$encryption->encryptString($value), 'Slack', '', 'slackOauthToken', $value]
            );
        }
    }
}
