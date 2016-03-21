<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Test Flow:
 *
 * Preconditions:
 * 1. Enable Gift Messages (Order/Items level)
 * 2. Create Product according dataset
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
    const TO_MAINTAIN = 'yes'; // Consider variation #2 to work correctly with Virtual products
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
