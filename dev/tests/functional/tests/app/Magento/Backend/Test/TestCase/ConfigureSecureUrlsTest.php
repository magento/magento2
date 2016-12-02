<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Backend\Test\Page\Adminhtml\SystemConfigEdit;
use Magento\Mtf\Util\Command\Cli\Cache;
use Magento\Mtf\Util\Command\Cli\StaticContent;

/**
 * Verify that Merchant can configure secure URLs for Storefront and/or Admin panel in order to improve Store security.
 *
 * Preconditions:
 *  # SSL on server is configured.
 *  # Secure URLs are disabled for Storefront & Admin (out-of-the-box Magento state).
 *
 * Steps:
 *  # Log in to Admin panel.
 *  # Go to "Stores > Configuration" page.
 *  # Select needed scope.
 *  # Go to "General > Web > Base URLs (Secure)" section.
 *  # Specify Base URL with Secure protocol in the same format as a Secure Base URL.
 *    (i) Make sure that Secure Base URL ends with a "/".
 *  # Enable Secure URLs for Storefront if there is a need.
 *  # Enable Secure URLs for Admin if there is a need.
 *  # Save the Config & refresh invalidated caches (Configuration, Page Cache).
 *  # Deploy static view files.
 *
 *  # If Secure URLs for Storefront were enabled:
 *      # Assert that https is used all over the Storefront.
 *      # Assert that static content is deployed validly (ex: JS functionality works on Storefront).
 *      # Assert that Customer is redirected to https if trying to access the page directly via http.
 *  # If secure URLs for Storefront were disabled:
 *      # Assert that http is used all over the Storefront.
 *      # Assert that static content is deployed validly (ex: JS functionality works on Storefront).
 *
 *  # If secure URLs for Admin were enabled:
 *      # Assert that https is used all over the Admin panel.
 *      # Assert that static content is deployed validly (ex: JS functionality works in Admin panel).
 *      # Assert that Merchant is redirected to https if trying to access the page directly via http.
 *  # If secure URLs for Admin were disabled:
 *      # Assert that http is used all over the Admin panel.
 *      # Assert that static content is deployed validly (ex: JS functionality works in Admin panel).
 *      # Assert that Merchant is redirected to http if trying to access the page directly via https.
 *
 * Postconditions:
 *  # Turn the Secure URLs usage off (with further cache refreshing & static content deploying).
 *
 * @ZephyrId MAGETWO-35408
 */
class ConfigureSecureUrlsTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * "Configuration" page in Admin panel.
     *
     * @var SystemConfigEdit
     */
    protected $configurationAdminPage;

    /**
     * Cache CLI.
     *
     * @var Cache
     */
    protected $cache;

    /**
     * Static content CLI.
     *
     * @var StaticContent
     */
    protected $staticContent;

    /**
     * Prepare data for further test execution.
     *
     * @param FixtureFactory $fixtureFactory
     * @param SystemConfigEdit $configurationAdminPage
     * @param Cache $cache
     * @param StaticContent $staticContent
     * @return void
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        SystemConfigEdit $configurationAdminPage,
        Cache $cache,
        StaticContent $staticContent
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->configurationAdminPage = $configurationAdminPage;
        $this->cache = $cache;
        $this->staticContent = $staticContent;
    }

    /**
     * Test execution.
     *
     * @param $configData
     * @return $this
     */
    public function test($configData)
    {
        $data = [
            'web/secure/base_url' => [
                'scope' => 'default',
                'scope_id' => 0,
                'value' => str_replace(['http', 'index.php/'], ['https', ''], $_ENV['app_frontend_url'])
            ]
        ];
        $config = $this->fixtureFactory->createByCode('configData', ['dataset' => $configData, 'data' => $data]);
        $config->persist();

        // Workaround until MTA-3879 is delivered.
        $this->configurationAdminPage->open();
        $this->configurationAdminPage->getForm()
            ->getGroup('web', 'secure')
            ->setValue('web', 'secure', 'use_in_adminhtml', 'Yes');
        $this->configurationAdminPage->getPageActions()->save();
        $_ENV['app_backend_url'] = str_replace('http', 'https', $_ENV['app_backend_url']);

        $this->cache->flush(['config', 'full_page']);
        $this->staticContent->deploy();
    }

    /**
     * Revert all applied high-level changes.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->configurationAdminPage->open();
        $this->configurationAdminPage->getForm()
            ->getGroup('web', 'secure')
            ->setValue('web', 'secure', 'use_in_adminhtml', 'No');
        $this->configurationAdminPage->getPageActions()->save();
        $this->cache->flush(['config', 'full_page']);
        $this->staticContent->deploy();
    }
}
