<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Slack;

use Piwik\Container\StaticContainer;
use Piwik\Http;
use Piwik\Log\LoggerInterface;

class ScheduleReportSlack
{
    private $subject;
    private $filename;

    private $fileContents;

    private $channel;

    private $fileID;

    private $token;

    private const SLACK_UPLOAD_URL_EXTERNAL = 'https://slack.com/api/files.getUploadURLExternal';
    private const SLACK_COMPLETE_UPLOAD_EXTERNAL = 'https://slack.com/api/files.completeUploadExternal';

    private const SLACK_TIMEOUT = 5000;

    private $logger;

    public function send(
        string $subject,
        string $fileName,
        string $fileContents,
        string $channel,
        #[\SensitiveParameter]
        string $token
    ) {
        $this->subject = $subject;
        $this->filename = $fileName;
        $this->fileContents = $fileContents;
        $this->channel = $channel;
        $this->token = $token;
        $this->logger = StaticContainer::get(LoggerInterface::class);

        $uploadURL = $this->getUploadURLExternal();
        if (!empty($uploadURL) && $this->sendFile($uploadURL)) {
            return $this->completeUploadExternal();
        }

        $this->logger->debug('Unable to send ' . $fileName . ' report to Slack');

        return false;
    }

    public function getUploadURLExternal(): string
    {
        try {
            $response = $this->sendHttpRequest(
                self::SLACK_UPLOAD_URL_EXTERNAL,
                self::SLACK_TIMEOUT,
                [
                    'token' => $this->token,
                    'filename' => $this->filename,
                    'length' => strlen($this->fileContents),
                ],
                ['Content-Type' => 'multipart/form-data']
            );
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            return '';
        }

        $data = json_decode($response, true);
        if (!empty($data) && !empty($data['upload_url']) && !empty($data['file_id'])) {
            $this->fileID = $data['file_id'];

            return $data['upload_url'];
        }

        return '';
    }

    public function sendFile(string $uploadURL): bool
    {
        try {
            $response = $this->sendHttpRequest(
                $uploadURL,
                self::SLACK_TIMEOUT,
                [$this->fileContents],
                [],
                true
            );
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            return false;
        }

        return stripos($response, 'OK') !== false;
    }

    public function completeUploadExternal(): bool
    {
        try {
            $response = $this->sendHttpRequest(
                self::SLACK_COMPLETE_UPLOAD_EXTERNAL,
                self::SLACK_TIMEOUT,
                [
                    'token' => $this->token,
                    'files' => json_encode([['id' => $this->fileID]]),
                    'channel_id' => $this->channel,
                    'initial_comment' => $this->subject
                ],
                ['Content-Type' => 'multipart/form-data']
            );
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            return false;
        }

        $data = json_decode($response, true);

        return !empty($data['ok']);
    }

    private function sendHttpRequest(string $url, int $timeout, array $requestBody, array $additionalHeaders = [], $requestBodyAsString = false)
    {
        if ($requestBodyAsString && !empty($requestBody[0])) {
            $requestBody = $requestBody[0];
        }

        return Http::sendHttpRequestBy(
            Http::getTransportMethod(),
            $url,
            $timeout,
            $userAgent = null,
            $destinationPath = null,
            $file = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $acceptInvalidSslCertificate = false,
            $byteRange = false,
            $getExtendedInfo = false,
            $httpMethod = 'POST',
            $httpUsername = null,
            $httpPassword = null,
            $requestBody,
            $additionalHeaders
        );
    }
}
