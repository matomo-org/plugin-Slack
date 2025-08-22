/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

 describe("Slack", function () {
   this.fixture = "Piwik\\Tests\\Fixtures\\EmptySite";

   // required to ensure no provider is set initially
   this.optionsOverride = {
     'persist-fixture-data': false,
   };

  async function captureScreen(screenshotName, theTest, selector) {
    if (!selector) {
      selector = reportSelector;
    }
    await theTest();
    await page.waitForTimeout(100);
    await page.waitForNetworkIdle();
    expect(await page.screenshotSelector(selector)).to.matchImage(screenshotName);
  }

  it('should load the schedule report as empty', async function () {
    const selector = '.page';
    await captureScreen('empty_report', async () => {
      await page.goto('?module=ScheduledReports&action=index&idSite=1&period=day&date=yesterday');
    }, selector);
  });

  it('should show create new report screen', async function () {
    const selector = '.page';
    await captureScreen('new_scheduled_reports', async () => {
      await page.goto('?module=ScheduledReports&action=index&idSite=1&period=day&date=yesterday');
      await page.evaluate(() => $('#add-report').click());
    }, selector);
  });

  it('should show send report via Slack as an option', async function () {
    const selector = '.page';
    await captureScreen('send_via_slack', async () => {
      await page.evaluate(() => $('#addEditReport .matomo-form-field:eq(6) input')[0].click());
    }, selector);
  });

  it('should show slack channel ID input as disabled as Oauth token not configured', async function () {
    const selector = '.page';
    await captureScreen('slack_report_disabled', async () => {
      await page.evaluate(() => $('#addEditReport .matomo-form-field:eq(6) ul li:last').click());
    }, selector);
  });

  it('should show report format for slack', async function () {
    const selector = '.page';
    await captureScreen('send_via_slack', async () => {
      await page.evaluate(() => $('#addEditReport .matomo-form-field.slack:eq(0) input')[0].click());
    }, selector);
  });

  it('should show slack channel ID input as enabled when Oauth token configured', async function () {
    const selector = '.page';
    testEnvironment.configOverride.Slack = {slackOauthToken: 'token'};
    testEnvironment.save();
    await page.goto('?module=ScheduledReports&action=index&idSite=1&period=day&date=yesterday');
    await page.waitForNetworkIdle();
    await captureScreen('slack_report_enabled', async () => {
      await page.evaluate(() => $('#add-report').click());
      await page.waitForNetworkIdle();
      await page.evaluate(() => $('#addEditReport .matomo-form-field:eq(6) input')[0].click());
      await page.evaluate(() => $('#addEditReport .matomo-form-field:eq(6) ul li:last').click());
    }, selector);
  });

  it('should show show error if channelID not set', async function () {
    const selector = '.page';
    testEnvironment.configOverride.Slack = {slackOauthToken: 'token'};
    testEnvironment.save();
    await captureScreen('slack_report_error', async () => {
      await page.evaluate(() => $('#report_description').val('Slack Report').change());
      await page.mouse.move(-10, -10);
      await page.mouse.click(-10, -10);
      await page.evaluate(() => $('#addEditReport .matomo-form-field.slack:eq(0) input')[0].click());
      await page.evaluate(() => $('#addEditReport .matomo-form-field.slack:eq(0) li:eq(1)').click());
      await page.evaluate(() => $('#slackVisitsSummary_get').click());
      await page.evaluate(() => $('.matomo-save-button input.btn').click());
      await page.waitForNetworkIdle();
    }, selector);
  });

  it('should show save a report successfully', async function () {
    const selector = '.page';
    await captureScreen('slack_report_save', async () => {
      await page.evaluate(() => $('#addEditReport .matomo-form-field.slack:eq(1) input').val('ChannelID').change());
      await page.mouse.move(-10, -10);
      await page.mouse.click(-10, -10);
      await page.evaluate(() => $('.matomo-save-button input.btn').click());
      await page.waitForNetworkIdle();
      await page.waitForTimeout(100);
    }, selector);
  });

});