<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * @security-private
 * Precondition:
 * 1. Customer is created.
 *
 * Steps:
 * 1. Log in as default admin user.
 * 2. Go to Customers > All Customers.
 * 3. Filter the customer created in pre-condition by customer email address.
 * 4. Open the filtered customer
 * 4. Select Create Order.
 * 5. Perform all assertions(assure that no alert pops up from create order page).
 *
 * @ZephyrId MAGETWO-64400
 */
class CreateCustomerBackendWithSpecialCaseLastNameEntityTest extends Scenario
{
    /* tags */
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * Runs create order from customer side on backend.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
