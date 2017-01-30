<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestCase;

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
 * 3. Click 'Invoice' button.
 * 4. Fill data according to dataset.
 * 5. Click 'Submit Invoice' button.
 * 6. Perform assertions.
 *
 * @group Order_Management_(CS)
 * @ZephyrId MAGETWO-28209
 */
class CreateInvoiceEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
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
            ['configData' => 'checkmo, flatrate']
        )->run();
    }

    /**
     * Create invoice.
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
        $result = $this->objectManager->create(
            'Magento\Sales\Test\TestStep\CreateInvoiceStep',
            ['order' => $order, 'data' => $data]
        )->run();

        return [
            'ids' => [
                'invoiceIds' => $result['invoiceIds'],
                'shipmentIds' => isset($result['shipmentIds']) ? $result['shipmentIds'] : null,
            ]
        ];
    }

    /**
     * Log out.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create('Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep')->run();
    }
}
