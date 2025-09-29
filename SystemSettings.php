<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Slack;

use Piwik\Piwik;
use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;
use Piwik\Url;

class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var Setting */
    public $slackOauthToken;

    protected function init()
    {
        // System setting --> allows selection of a single value
        $this->slackOauthToken = $this->createSlackOauthTokenSetting();
    }

    private function createSlackOauthTokenSetting()
    {
        return $this->makeSetting('slackOauthToken', $default = '', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = Piwik::translate('Slack_OauthTokenSettingTitle');
            $field->uiControl = FieldConfig::UI_CONTROL_PASSWORD;
            $link = Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/reports/how-to-get-the-slack-oauth-token-for-matomo-integration/', null, null, 'App.SystemSettings.Slack') . '#step-1-get-a-slack-oauth-token';
            $field->inlineHelp = Piwik::translate('Slack_OauthTokenSettingDescription', ['<a href="' . $link . '" target="_blank" rel="noreferrer noopener">', '</a>']);
            $field->transform = function ($value) {
                return trim($value);
            };
        });
    }
}
