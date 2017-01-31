<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Create Product according dataset.
 * 2. Enable Gift Messages (Order/Items level).
 *
 * Steps:
 * 1. Login to backend
 * 2. Go to Sales >Orders
 * 3. Create new order
 * 4. Fill data form dataset
 * 5. Perform all asserts
 *
 * @group Gift_Messages
 * @ZephyrId MAGETWO-29642
 */
class CreateGiftMessageOnBackendTest extends Scenario
{
    /* tags */
    const MVP = 'no';
    const SEVERITY = 'S2';
    const TO_MAINTAIN = 'yes';
    /* end tags */

    /**
     * Run CreateGiftMessageOnBackend test.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
