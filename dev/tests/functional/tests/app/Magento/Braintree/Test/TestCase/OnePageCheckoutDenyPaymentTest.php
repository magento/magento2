<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Class OnePageCheckoutDenyPaymentTest
 *
 * This scenario places order via Braintree payment with
 * enabled Advanced Fraud protection and deny payment for placed order
 */
class OnePageCheckoutDenyPaymentTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test, 3rd_party_test';
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
