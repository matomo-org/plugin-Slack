/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

 describe("SlackSystemSetting", function () {
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

  it('should load the Settings page for Slack correctly', async function () {
    const selector = '.card-content:contains(Slack)';
    await captureScreen('settings_page', async () => {
      await page.goto('?module=CoreAdminHome&action=generalSettings&idSite=1&period=day&date=yesterday');
    }, selector);
  });

});