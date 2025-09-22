/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

 describe("CustomAlerts", function () {
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
    await page.waitForNetworkIdle();
    await page.waitForSelector(selector);
    expect(await page.screenshotSelector(selector)).to.matchImage(screenshotName);
  }

  before(function () {
    testEnvironment.pluginsToLoad = ['CustomAlerts', 'Slack'];
    testEnvironment.save();
  });

  it('should load the custom alerts as empty', async function () {
    const selector = '.page';
    await captureScreen('empty_report', async () => {
      await page.goto('?module=CustomAlerts&action=index&idSite=1&period=day&date=yesterday');
    }, selector);
  });

  it('should show load a new alert add screen', async function () {
    const selector = '.page';
    await captureScreen('new_custom_alert', async () => {
      await page.evaluate(() => $('.icon-add').click());
    }, selector);
  });

  it('should show send alert via Slack as an option enabled', async function () {
    const selector = '.page';
    await captureScreen('send_via_slack_new', async () => {
      await page.click('.report-mediums .select-dropdown');
      await page.waitForNetworkIdle();
      await page.waitForTimeout(350); // wait for animation
    }, selector);
    await page.click('.report-mediums .select-dropdown');
  });

  it('should show slack channel ID input as disabled as Oauth token not configured', async function () {
    const selector = '.page';
    await captureScreen('slack_report_disabled', async () => {
      await page.evaluate(() => $('.report-mediums .select-wrapper ul li:contains("Slack")').click());
    }, selector);
  });

  it('should show slack channel ID input as enabled when Oauth token configured', async function () {
    const selector = '.page';
    testEnvironment.configOverride.Slack = {slackOauthToken: 'token'};
    testEnvironment.save();
    await page.goto('?module=CustomAlerts&action=addNewAlert&idSite=1&period=day&date=yesterday');
    await page.waitForNetworkIdle();
    await captureScreen('slack_report_enabled', async () => {
      await page.evaluate(() => $('.report-mediums .select-wrapper ul li:contains("Slack")').click());
    }, selector);
  });

  it('should show show error if channelID not set', async function () {
    const selector = '.page';
    testEnvironment.configOverride.Slack = {slackOauthToken: 'token'};
    testEnvironment.save();
    await captureScreen('slack_report_error', async () => {
      await page.type('#alertName', 'Test Slack Alert');
      await page.evaluate(() => $('.conditionAndValue .select-wrapper ul li:last').click());
      await page.type('#metricValue', '2');
      await page.click('.matomo-save-button input.btn');
      await page.waitForNetworkIdle();
    }, selector);
  });

  it('should save a report successfully', async function () {
    const selector = '.page';
    testEnvironment.configOverride.Slack = {slackOauthToken: 'token'};
    testEnvironment.save();
    await captureScreen('slack_alert_report_save_success', async () => {
      await page.type('input#channelID', 'ChannelID');
      await page.click('.matomo-save-button input.btn');
      await page.waitForNetworkIdle();
    }, selector);
  });

  });