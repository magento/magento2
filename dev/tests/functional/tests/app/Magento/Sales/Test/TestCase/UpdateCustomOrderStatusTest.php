<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestCase;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Fixture\OrderStatus;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\OrderStatusEdit;
use Magento\Sales\Test\Page\Adminhtml\OrderStatusIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Custom Order Status is created.
 * 2. New order should be created if orderExist=Yes.
 *
 * Steps:
 * 1. Log in as admin.
 * 2. Navigate to the Stores > Settings > Order Status.
 * 3. Click on Custom Order Status from grid.
 * 4. Fill in all data according to data set.
 * 5. Save order status.
 * 6. Perform all assertions.
 *
 * @group Order_Management
 * @ZephyrId MAGETWO-29868
 */
class UpdateCustomOrderStatusTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Order Status Index page.
     *
     * @var OrderStatusIndex
     */
    protected $orderStatusIndex;

    /**
     * Order Status Edit page.
     *
     * @var OrderStatusEdit
     */
    protected $orderStatusEdit;

    /**
     * Order Index page.
     *
     * @var OrderIndex
     */
    protected $orderIndex;

    /**
     * OrderStatus Fixture.
     *
     * @var OrderStatus
     */
    protected $orderStatus;

    /**
     * OrderStatus Fixture.
     *
     * @var OrderStatus
     */
    protected $orderStatusInitial;

    /**
     * OrderInjectable Fixture.
     *
     * @var OrderInjectable
     */
    protected $order;

    /**
     * Injection data.
     *
     * @param OrderStatusIndex $orderStatusIndex
     * @param OrderStatusEdit $orderStatusEdit
     * @param OrderIndex $orderIndex
     * @return void
     */
    public function __inject(
        OrderStatusIndex $orderStatusIndex,
        OrderStatusEdit $orderStatusEdit,
        OrderIndex $orderIndex
    ) {
        $this->orderStatusIndex = $orderStatusIndex;
        $this->orderStatusEdit = $orderStatusEdit;
        $this->orderIndex = $orderIndex;
    }

    /**
     * Run Update Custom OrderStatus Test.
     *
     * @param OrderStatus $orderStatusInitial
     * @param OrderStatus $orderStatus
     * @param OrderInjectable $order
     * @param FixtureFactory $fixtureFactory
     * @param string $orderExist
     * @return array
     */
    public function test(
        OrderStatus $orderStatusInitial,
        OrderStatus $orderStatus,
        OrderInjectable $order,
        FixtureFactory $fixtureFactory,
        $orderExist
    ) {
        // Preconditions:
        $orderStatusInitial->persist();
        if ($orderExist == 'Yes') {
            $config = $fixtureFactory->createByCode('configData', [
                'dataset' => 'checkmo_custom_new_order_status',
                'data' => ['payment/checkmo/order_status' => ['value' => $orderStatusInitial->getStatus()]]
            ]);
            $config->persist();
            $order->persist();
        }
        // Steps:
        $this->orderStatusIndex->open();
        $this->orderStatusIndex->getOrderStatusGrid()->searchAndOpen(['label' => $orderStatusInitial->getLabel()]);
        $this->orderStatusEdit->getOrderStatusForm()->fill($orderStatus);
        $this->orderStatusEdit->getFormPageActions()->save();

        // Configuring orderStatus for asserts.
        $orderStatus = $fixtureFactory->createByCode(
            'orderStatus',
            ['data' => array_merge($orderStatusInitial->getData(), $orderStatus->getData())]
        );

        // Prepare data for tear down
        $this->orderStatus = $orderStatus;
        $this->orderStatusInitial = $orderStatusInitial;
        $this->order = $order;

        return [
            'orderStatus' => $orderStatus,
            'status' => $orderStatus->getLabel(),
            'customer' => $order->getDataFieldConfig('customer_id')['source']->getCustomer()
        ];
    }

    /**
     * Change created order status and unassign custom order status if order was created.
     *
     * @return void
     */
    public function tearDown()
    {
        if ($this->order->hasData('id')) {
            $this->orderIndex->open()->getSalesOrderGrid()->massaction([['id' => $this->order->getId()]], 'Cancel');
            $filter = ['label' => $this->orderStatus->getLabel(), 'status' => $this->orderStatusInitial->getStatus()];
            $this->orderStatusIndex->open()->getOrderStatusGrid()->searchAndUnassign($filter);
            $this->objectManager->create(
                \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
                ['configData' => 'checkmo_custom_new_order_status_rollback']
            )->run();
        }
    }
}
