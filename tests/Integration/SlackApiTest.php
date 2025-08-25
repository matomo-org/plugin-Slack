<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Slack\tests;

use Piwik\Plugins\Slack\SlackApi;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class SlackApiTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testSendShouldReturnFalseWhenNoUploadURLisReturned()
    {
        $helperMock = $this->getMockBuilder(SlackApi::class)
            ->setMethods([
                'getUploadURLExternal',
                'sendFile',
                'completeUploadExternal'
            ])
            ->setConstructorArgs(array('token'))
            ->getMock();
        $helperMock->expects($this->once())->method('getUploadURLExternal')->willReturn('');
        $helperMock->expects($this->never())->method('sendFile');
        $helperMock->expects($this->never())->method('completeUploadExternal');

        $this->assertFalse($helperMock->uploadFile('test file', 'test.csv', "a,b,c", 'channelID'));
    }

    public function testSendShouldReturnFalseWhenSendFileFails()
    {
        $helperMock = $this->getMockBuilder(SlackApi::class)
            ->setMethods([
                'getUploadURLExternal',
                'sendFile',
                'completeUploadExternal'
            ])
            ->setConstructorArgs(array('token'))
            ->getMock();
        $helperMock->expects($this->once())->method('getUploadURLExternal')->willReturn('https://SLACL_URL');
        $helperMock->expects($this->once())->method('sendFile')->willReturn(false);
        $helperMock->expects($this->never())->method('completeUploadExternal');

        $this->assertFalse($helperMock->uploadFile('test file', 'test.csv', "a,b,c", 'channelID'));
    }

    public function testSendShouldReturnFalseWhenCompleteUploadExternalFails()
    {
        $helperMock = $this->getMockBuilder(SlackApi::class)
            ->setMethods([
                'getUploadURLExternal',
                'sendFile',
                'completeUploadExternal'
            ])
            ->setConstructorArgs(array('token'))
            ->getMock();
        $helperMock->expects($this->once())->method('getUploadURLExternal')->willReturn('https://SLACL_URL');
        $helperMock->expects($this->once())->method('sendFile')->willReturn(true);
        $helperMock->expects($this->once())->method('completeUploadExternal')->willReturn(false);

        $this->assertFalse($helperMock->uploadFile('test file', 'test.csv', "a,b,c", 'channelID'));
    }

    public function testSendSuccess()
    {
        $helperMock = $this->getMockBuilder(SlackApi::class)
            ->setMethods([
                'getUploadURLExternal',
                'sendFile',
                'completeUploadExternal'
            ])
            ->setConstructorArgs(array('token'))
            ->getMock();
        $helperMock->expects($this->once())->method('getUploadURLExternal')->willReturn('https://SLACL_URL');
        $helperMock->expects($this->once())->method('sendFile')->willReturn(true);
        $helperMock->expects($this->once())->method('completeUploadExternal')->willReturn(true);

        $this->assertTrue($helperMock->uploadFile('test file', 'test.csv', "a,b,c", 'channelID'));
    }
}
