<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Complete a sales order with online payment method.
 *
 * Steps:
 * 1. Go to Order from PreConditions Detail Page.
 * 2. Open Invoice.
 * 3. Click "Credit Memo" button on the Invoice page.
 * 4. Click "Refund".
 * 5. Go to "Credit Memos" tab.
 * 6. Go to "Transactions" tab.
 * 7. Perform assertions.
 *
 * @group Paypal
 * @ZephyrId MAGETWO-13061
 */
class CreateOnlineCreditMemoPayflowLinkTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = '3rd_party_test';
    const SEVERITY = 'S0';
    /* end tags */

    /**
     * Create Refund for Order Paid with PayPal Payflow Link.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
