<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Enable "Terms and Conditions": Stores > Configuration > Sales > Checkout > Checkout Options
 *
 * Steps:
 * 1. Open Backend Stores > Terms and Conditions
 * 2. Create new "Terms and Conditions"
 * 3. Fill data from dataset
 * 4. Save
 * 5. Perform all assertions
 *
 * @group Terms_and_Conditions
 * @ZephyrId MAGETWO-29586, MAGETWO-32499
 */
class CreateTermEntityTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'extended_acceptance_test';
    const SEVERITY = 'S3';
    /* end tags */

    /**
     * Create term entity test.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
