<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\TestCase;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Enable payment method "Check/Money Order".
 * 2. Enable shipping method one of "Flat Rate/Free Shipping".
 * 3. Create order.
 *
 * Steps:
 * 1. Go to Sales > Orders.
 * 2. Select created order in the grid and open it.
 * 3. Click 'Ship' button.
 * 4. Fill data according to dataset.
 * 5. Click 'Submit Shipment' button.
 * 6. Perform all asserts.
 *
 * @group Order_Management_(CS)
 * @ZephyrId MAGETWO-28708
 */
class CreateShipmentEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    const TEST_TYPE = 'extended_acceptance_test';
    /* end tags */

    /**
     * Set up configuration.
     *
     * @return void
     */
    public function __prepare()
    {
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => "checkmo,flatrate"]
        )->run();
    }

    /**
     * Create shipment.
     *
     * @param OrderInjectable $order
     * @param array $data
     * @return array
     */
    public function test(OrderInjectable $order, array $data)
    {
        // Preconditions
        $order->persist();

        // Steps
        $createShipping = $this->objectManager->create(
            'Magento\Sales\Test\TestStep\CreateShipmentStep',
            ['order' => $order, 'data' => $data]
        );

        return ['ids' => $createShipping->run()];
    }
}
