<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

class OnePageCheckoutPaymentMethodDataPersistenceWithDiscountTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test, 3rd_party_test';
    /* end tags */

    /**
     * Runs one page checkout test.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
