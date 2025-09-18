<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Slack;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\CustomAlerts\Controller;

class EnrichTriggeredAlerts extends Controller
{
    public function __construct()
    {
        parent::__construct(StaticContainer::get('Piwik\Plugins\API\ProcessedReport'));
    }

    public function enrichTriggeredAlerts($triggeredAlerts)
    {
        return parent::enrichTriggeredAlerts($triggeredAlerts);
    }
}
