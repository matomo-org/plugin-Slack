<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Slack\tests;

use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\Slack\ScheduleReportSlack;

class ScheduleReportSlackTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testSendShouldReturnFalseWhenNoUploadURLisReturned()
    {
        $helperMock = $this->createPartialMock(ScheduleReportSlack::class, [
            'getUploadURLExternal',
            'sendFile',
            'completeUploadExternal'
        ]);
        $helperMock->expects($this->once())->method('getUploadURLExternal')->willReturn('');
        $helperMock->expects($this->never())->method('sendFile');
        $helperMock->expects($this->never())->method('completeUploadExternal');

        $this->assertFalse($helperMock->send('test file', 'test.csv', "a,b,c", 'channelID', 'token'));
    }

    public function testSendShouldReturnFalseWhenSendFileFails()
    {
        $helperMock = $this->createPartialMock(ScheduleReportSlack::class, [
            'getUploadURLExternal',
            'sendFile',
            'completeUploadExternal'
        ]);
        $helperMock->expects($this->once())->method('getUploadURLExternal')->willReturn('https://SLACL_URL');
        $helperMock->expects($this->once())->method('sendFile')->willReturn(false);
        $helperMock->expects($this->never())->method('completeUploadExternal');

        $this->assertFalse($helperMock->send('test file', 'test.csv', "a,b,c", 'channelID', 'token'));
    }

    public function testSendShouldReturnFalseWhenCompleteUploadExternalFails()
    {
        $helperMock = $this->createPartialMock(ScheduleReportSlack::class, [
            'getUploadURLExternal',
            'sendFile',
            'completeUploadExternal'
        ]);
        $helperMock->expects($this->once())->method('getUploadURLExternal')->willReturn('https://SLACL_URL');
        $helperMock->expects($this->once())->method('sendFile')->willReturn(true);
        $helperMock->expects($this->once())->method('completeUploadExternal')->willReturn(false);

        $this->assertFalse($helperMock->send('test file', 'test.csv', "a,b,c", 'channelID', 'token'));
    }

    public function testSendSuccess()
    {
        $helperMock = $this->createPartialMock(ScheduleReportSlack::class, [
            'getUploadURLExternal',
            'sendFile',
            'completeUploadExternal'
        ]);
        $helperMock->expects($this->once())->method('getUploadURLExternal')->willReturn('https://SLACL_URL');
        $helperMock->expects($this->once())->method('sendFile')->willReturn(true);
        $helperMock->expects($this->once())->method('completeUploadExternal')->willReturn(true);

        $this->assertTrue($helperMock->send('test file', 'test.csv', "a,b,c", 'channelID', 'token'));
    }
}
