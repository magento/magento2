<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestCase;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Preconditions:
 * 1. Enable payment method: "Check/Money Order/Bank Transfer/Cash on Delivery/Purchase Order/Zero Subtotal Checkout".
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
 * @group Order_Management
 * @ZephyrId MAGETWO-28209
 */
class CreateInvoiceEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const STABLE = 'no';
    /* end tags */

    /**
     * Factory for Test Steps.
     *
     * @var TestStepFactory
     */
    protected $stepFactory;

    /**
     * Prepare data.
     *
     * @param TestStepFactory $stepFactory
     * @return void
     */
    public function __prepare(TestStepFactory $stepFactory)
    {
        $this->stepFactory = $stepFactory;
    }

    /**
     * Create invoice.
     *
     * @param OrderInjectable $order
     * @param array $data
     * @param string $configData
     * @return array
     */
    public function test(OrderInjectable $order, array $data, $configData)
    {
        // Preconditions
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $configData]
        )->run();
        $order->persist();

        // Steps
        $result = $this->stepFactory->create(
            \Magento\Sales\Test\TestStep\CreateInvoiceStep::class,
            ['order' => $order, 'data' => $data]
        )->run();

        return $result;
    }

    /**
     * Log out.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->stepFactory->create(\Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep::class)->run();
    }
}
