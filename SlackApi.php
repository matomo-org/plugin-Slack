<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Slack;

use Piwik\Container\StaticContainer;
use Piwik\Http;
use Piwik\Log\LoggerInterface;

class SlackApi
{
    /**
     * @var string
     */
    private $fileID;

    /**
     * @var string
     */
    private $token;

    /**
     * @var LoggerInterface
     */
    private $logger;

    private const SLACK_UPLOAD_URL_EXTERNAL = 'https://slack.com/api/files.getUploadURLExternal';
    private const SLACK_COMPLETE_UPLOAD_EXTERNAL = 'https://slack.com/api/files.completeUploadExternal';
    private const SLACK_POST_MESSAGE_URL = 'https://slack.com/api/chat.postMessage';

    private const SLACK_TIMEOUT = 5;

    public function __construct(
        #[\SensitiveParameter]
        string $token
    ) {
        $this->token = $token;
        $this->logger = StaticContainer::get(LoggerInterface::class);
    }

    /**
     * Slack file upload is done in 3 parts - https://api.slack.com/messaging/files
     *
     * 1. Call files.getUploadURLExternal. The response will contain a URL that you can POST the contents of your file to
     * 2. POST the contents of your file to the URL returned in step 1
     * 3. Call files.completeUploadExternal along with ChannelId to post the file to a Channel.
     */
    public function uploadFile(string $subject, string $fileName, string $fileContents, string $channel): bool
    {
        $uploadURL = $this->getUploadURLExternal($fileName, strlen($fileContents));
        if (!empty($uploadURL) && $this->sendFile($uploadURL, $fileContents)) {
            return $this->completeUploadExternal($channel, $subject);
        }

        $this->logger->info('Unable to send ' . $fileName . ' report to Slack');

        return false;
    }

    /**
     *
     * Get the URL to upload the file
     *
     * @param string $fileName
     * @param int $fileLength
     * @return string
     */
    public function getUploadURLExternal(string $fileName, int $fileLength): string
    {
        try {
            $response = $this->sendHttpRequest(
                self::SLACK_UPLOAD_URL_EXTERNAL,
                self::SLACK_TIMEOUT,
                [
                    'token' => $this->token,
                    'filename' => $fileName,
                    'length' => $fileLength,
                ],
                ['Content-Type' => 'multipart/form-data']
            );
        } catch (\Exception $e) {
            $this->logger->error('Slack error getUploadURLExternal: ' . $e->getMessage());
            return '';
        }

        $data = json_decode($response, true);
        if (!empty($data) && !empty($data['upload_url']) && !empty($data['file_id'])) {
            $this->fileID = $data['file_id'];

            return $data['upload_url'];
        }

        return '';
    }

    /**
     *
     * Upload the file contents to the URL received from getUploadURLExternal method
     *
     * @param string $uploadURL
     * @param string $fileContents
     * @return bool
     */
    public function sendFile(string $uploadURL, string $fileContents): bool
    {
        try {
            $response = $this->sendHttpRequest(
                $uploadURL,
                self::SLACK_TIMEOUT,
                [$fileContents],
                [],
                true
            );
        } catch (\Exception $e) {
            $this->logger->error('Slack error sendFile: ' . $e->getMessage());
            return false;
        }

        return strtolower($response) === ('ok - ' . strlen($fileContents));
    }


    /**
     *
     * Post the uploaded file to a channel
     *
     * @param string $channel
     * @param string $subject
     * @return bool
     */
    public function completeUploadExternal(string $channel, string $subject): bool
    {
        try {
            $response = $this->sendHttpRequest(
                self::SLACK_COMPLETE_UPLOAD_EXTERNAL,
                self::SLACK_TIMEOUT,
                [
                    'token' => $this->token,
                    'files' => json_encode([['id' => $this->fileID]]),
                    'channels' => $channel,
                    'initial_comment' => $subject
                ],
                ['Content-Type' => 'multipart/form-data']
            );
        } catch (\Exception $e) {
            $this->logger->error('Slack error completeUploadExternal:' . $e->getMessage());
            return false;
        }

        $data = json_decode($response, true);

        return !empty($data['ok']);
    }

    /**
     *
     * Send a text message to a Slack channel
     *
     * @param string $message
     * @param string $channel
     * @return bool
     */
    public function sendMessage(string $message, string $channel): bool
    {
        if (empty($message) || empty($channel)) {
            $this->logger->info('Empty message or channel for sending message');
            return false;
        }

        try {
            $response = $this->sendHttpRequest(
                self::SLACK_POST_MESSAGE_URL,
                self::SLACK_TIMEOUT,
                [
                    'token' => $this->token,
                    'channel' => $channel,
                    'text' => $message,
                ],
                ['Content-Type' => 'multipart/form-data']
            );
        } catch (\Exception $e) {
            $this->logger->error('Slack error sendMessage:' . $e->getMessage());
            return false;
        }

        $data = json_decode($response, true);

        return !empty($data['ok']);
    }

    /**
     *
     * Wrapper to send HTTP request
     *
     * @param string $url
     * @param int $timeout
     * @param array $requestBody
     * @param array $additionalHeaders
     * @param $requestBodyAsString
     * @return array|bool|int[]|string
     * @throws \Exception
     */
    public function sendHttpRequest(string $url, int $timeout, array $requestBody, array $additionalHeaders = [], $requestBodyAsString = false)
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
