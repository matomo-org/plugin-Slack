<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Slack;

class ScheduleReportSlack
{
    /**
     * @var string
     */
    private $subject;
    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $fileContents;

    /**
     * @var string
     */
    private $channel;

    /**
     * @var string
     */
    private $token;

    public function __construct(
        string $subject,
        string $fileName,
        string $fileContents,
        string $channel,
        #[\SensitiveParameter]
        string $token
    ) {
        $this->subject = $subject;
        $this->fileName = $fileName;
        $this->fileContents = $fileContents;
        $this->channel = $channel;
        $this->token = $token;
    }

    public function send(): bool
    {
        $slackApi = new SlackApi($this->token);
        return $slackApi->uploadFile($this->subject, $this->fileName, $this->fileContents, $this->channel);
    }
}
