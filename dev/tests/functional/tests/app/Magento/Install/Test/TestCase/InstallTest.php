<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Install\Test\TestCase;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Install\Test\Page\Install;
use Magento\Install\Test\Fixture\Install as InstallConfig;
use Magento\User\Test\Fixture\User;
use Mtf\Fixture\FixtureFactory;
use Mtf\TestCase\Injectable;
use Mtf\System\Config;
use Magento\Install\Test\Constraint\AssertAgreementTextPresent;
use Magento\Install\Test\Constraint\AssertSuccessfulReadinessCheck;

/**
 * PLEASE ADD NECESSARY INFO BEFORE RUNNING TEST TO
 * ../dev/tests/functional/config/install_data.yml.dist
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Uninstall Magento.
 *
 * Steps:
 * 1. Go setup landing page.
 * 2. Click on "Terms and agreements" button.
 * 3. Check license agreement text.
 * 4. Return back to landing page and click "Agree and Setup" button.
 * 5. Click "Start Readiness Check" button.
 * 6. Make sure PHP Version, PHP Extensions and File Permission are ok.
 * 7. Click "Next" and fill DB credentials.
 * 8. Click "Test Connection and Authentication" and make sure connection successful.
 * 9. Click "Next" and fill store address and admin path.
 * 10. Click "Next" and leave all default values.
 * 11. Click "Next" and fill admin user info.
 * 12. Click "Next" and on the "Step 6: Install" page click "Install Now" button.
 * 13. Perform assertions.
 *
 * @group Installer_and_Upgrade/Downgrade_(PS)
 * @ZephyrId MAGETWO-31431
 */
class InstallTest extends Injectable
{
    /**
     * Install page.
     *
     * @var Install
     */
    protected $installPage;

    /**
     * Cms index page.
     *
     * @var CmsIndex
     */
    protected $homePage;

    /**
     * Uninstall Magento before test.
     *
     * @param Config $systemConfig
     * @return array
     */
    public function __prepare(Config $systemConfig)
    {
        // Prepare config data
        $configData = $systemConfig->getConfigParam('install_data/db_credentials');
        $urlConfig = $systemConfig->getConfigParam('install_data/url');
        $configData['web'] = $urlConfig['base_url'];
        $configData['admin'] = $urlConfig['backend_frontname'];

        return ['configData' => $configData];
    }

    /**
     * Injection data.
     *
     * @param CmsIndex $homePage
     * @param Install $installPage
     * @return void
     */
    public function __inject(Install $installPage, CmsIndex $homePage)
    {
        $magentoBaseDir = dirname(dirname(dirname(MTF_BP)));
        // Uninstall Magento.
        shell_exec("php -f $magentoBaseDir/setup/index.php uninstall");
        $this->installPage = $installPage;
        $this->homePage = $homePage;
    }

    /**
     * Install Magento via web interface.
     *
     * @param User $user
     * @param array $install
     * @param array $configData
     * @param FixtureFactory $fixtureFactory
     * @param AssertAgreementTextPresent $assertLicense
     * @param AssertSuccessfulReadinessCheck $assertReadiness
     * @return array
     */
    public function test(
        User $user,
        array $install,
        array $configData,
        FixtureFactory $fixtureFactory,
        AssertAgreementTextPresent $assertLicense,
        AssertSuccessfulReadinessCheck $assertReadiness
    ) {
        $dataConfig = array_merge($install, $configData);
        if ($dataConfig['httpsFront'] != "-") {
            $dataConfig['https'] = str_replace('http', 'https', $dataConfig['web']);
        }
        /** @var InstallConfig $installConfig */
        $installConfig = $fixtureFactory->create('Magento\Install\Test\Fixture\Install', ['data' => $dataConfig]);
        // Steps
        $this->homePage->open();
        // Verify license agreement.
        $this->installPage->getLandingBlock()->clickTermsAndAgreement();
        $assertLicense->processAssert($this->installPage);
        $this->installPage->getLicenseBlock()->clickBack();
        $this->installPage->getLandingBlock()->clickAgreeAndSetup();
        // Step 1: Readiness Check.
        $this->installPage->getReadinessBlock()->clickReadinessCheck();
        $assertReadiness->processAssert($this->installPage);
        $this->installPage->getReadinessBlock()->clickNext();
        // Step 2: Add a Database.
        $this->installPage->getDatabaseBlock()->fill($installConfig);
        $this->installPage->getDatabaseBlock()->clickNext();
        // Step 3: Web Configuration.
        $this->installPage->getWebConfigBlock()->clickAdvancedOptions();
        $this->installPage->getWebConfigBlock()->fill($installConfig);
        $this->installPage->getWebConfigBlock()->clickNext();
        // Step 4: Customize Your Store
        $this->installPage->getCustomizeStoreBlock()->fill($installConfig);
        $this->installPage->getCustomizeStoreBlock()->clickNext();
        // Step 5: Create Admin Account.
        $this->installPage->getCreateAdminBlock()->fill($user);
        $this->installPage->getCreateAdminBlock()->clickNext();
        // Step 6: Install.
        $this->installPage->getInstallBlock()->clickInstallNow();

        return ['installConfig' => $installConfig];
    }
}
