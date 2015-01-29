<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Test Flow:
 *
 * Preconditions:
 * 1. Create Product according dataSet.
 * 2. Enable Gift Messages (Order/Items level).
 *
 * Steps:
 * 1. Login to backend
 * 2. Go to Sales >Orders
 * 3. Create new order
 * 4. Fill data form dataSet
 * 5. Perform all asserts
 *
 * @group Gift_Messages_(CS)
 * @ZephyrId MAGETWO-29642
 */
class CreateGiftMessageOnBackendTest extends Scenario
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'CS';
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

    /**
     * Disable enabled config after test.
     *
     * @return void
     */
    public function tearDown()
    {
        $setConfigStep = $this->objectManager->create(
            'Magento\Core\Test\TestStep\SetupConfigurationStep',
            ['configData' => 'cashondelivery', 'rollback' => true]
        );
        $setConfigStep->run();
    }
}
