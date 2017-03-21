<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. PLace order via Briantree PayPal.
 *
 * Steps:
 * 1.  Log in to Admin.
 * 2.  Open created order.
 * 3.  Create credit memo.
 * 4. Perform assertions.
 *
 * @group Braintree
 * @ZephyrId MAGETWO-48689, MAGETWO-48698
 */
class CreateOnlineCreditMemoBraintreePaypalTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = '3rd_party_test';
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * Runs test for online credit memo creation for order placed via Braintree PayPal.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
