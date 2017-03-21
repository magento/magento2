<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Complete a sales order with online payment method.
 *
 * Steps:
 * 1. Log in to Admin.
 * 2. Open order from preconditions.
 * 3. Open created invoice.
 * 3. Create credit memo.
 * 4. Perform assertions.
 *
 * @group Order_Management
 * @ZephyrId MAGETWO-13059
 */
class CreateOnlineCreditMemoTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = '3rd_party_test';
    const SEVERITY = 'S0';
    /* end tags */

    /**
     * Runs test for online credit memo creation for order placed with online payment method.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
