<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestCase;

use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Create products.
 * 2. Enable all Gift Options.
 * 3. Create Gift Card Account with Balance = 1.
 * 4. Create Customer Account.
 * 5. Place order with options according to dataset.
 *
 * Steps:
 * 1. Find the Order on frontend.
 * 2. Navigate to: Orders and Returns.
 * 3. Fill the form with correspondent Order data.
 * 4. Click on the "Continue" button.
 * 5. Click on the "Print Order" button.
 * 6. Perform appropriate assertions.v
 *
 * @group Order_Management_(CS)
 * @ZephyrId MAGETWO-30253
 */
class PrintOrderFrontendGuestTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Browser.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Prepare data.
     *
     * @param BrowserInterface $browser
     */
    public function __prepare(BrowserInterface $browser)
    {
        $this->browser = $browser;
    }

    /**
     * Runs print order on frontend.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }

    /**
     * Close browser.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->browser->closeWindow();
    }
}
