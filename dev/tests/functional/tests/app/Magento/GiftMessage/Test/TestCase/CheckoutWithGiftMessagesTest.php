<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\GiftMessage\Test\TestCase;

use Mtf\TestCase\Scenario;

/**
 * Test Creation for Checkout with Gift Messages
 *
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
    /**
     * Runs one page checkout test with gift message.
     *
     * @return void
     */
    public function test()
    {
        $this->markTestIncomplete("Bug: MAGETWO-30593");
        $this->executeScenario();
    }
}
