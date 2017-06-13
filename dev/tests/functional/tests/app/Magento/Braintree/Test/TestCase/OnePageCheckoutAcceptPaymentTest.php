<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Order is placed with Braintree Credit Card from Storefront with Advanced Fraud Protection.
 *
 * Steps:
 * 1. Log in to Admin.
 * 2. Go to Sales > Orders page.
 * 3. Open the placed order.
 * 4. Click Accept button.
 * 5. Perform assertions.
 *
 * @group Braintree
 * @ZephyrId MAGETWO-56023
 */
class OnePageCheckoutAcceptPaymentTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test, 3rd_party_test';
    const SEVERITY = 'S2';
    /* end tags */

    /**
     * Runs one page checkout test.
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
