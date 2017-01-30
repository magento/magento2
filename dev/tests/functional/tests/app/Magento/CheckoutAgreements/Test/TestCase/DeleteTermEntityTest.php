<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Enable "Terms and Conditions": Stores > Configuration > Sales > Checkout > Checkout Options.
 * 2. Create term according to dataset.
 *
 * Steps:
 * 1. Open Backend Stores > Terms and Conditions.
 * 2. Open created Term from preconditions.
 * 3. Click on 'Delete' button.
 * 4. Perform all assertions.
 *
 * @group Terms_and_Conditions_(CS)
 * @ZephyrId MAGETWO-29687
 */
class DeleteTermEntityTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Delete Term Entity test.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
