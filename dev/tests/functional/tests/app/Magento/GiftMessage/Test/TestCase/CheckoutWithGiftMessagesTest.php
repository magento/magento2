<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Test Flow:
 *
 * Preconditions:
 * 1. Enable Gift Messages (Order/Items level)
 * 2. Create Product according dataSet
 *
 * Steps:
 * 1. Login as registered customer
 * 2. Add product to Cart and start checkout
 * 3. On Shipping Method section Click "Add gift option"
 * 4. Complete Checkout steps
 * 5. Perform all asserts
 *
 * @group Gift_Messages_(CS)
 * @ZephyrId MAGETWO-28978
 */
class CheckoutWithGiftMessagesTest extends Scenario
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Runs one page checkout test with gift message.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
