<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestCase;

use Magento\Sales\Test\Constraint\AssertOrderStatusSuccessAssignMessage;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Fixture\OrderStatus;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\OrderStatusAssign;
use Magento\Sales\Test\Page\Adminhtml\OrderStatusIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Custom Order Status is created.
 *
 * Steps:
 * 1. Log in as admin.
 * 2. Navigate to the Stores > Settings > Order Status.
 * 3. Click on "Assign Status to State.
 * 4. Fill in all data according to data set.
 * 5. Save Status Assignment.
 * 6. Call assert assertOrderStatusSuccessAssignMessage.
 * 7. Create Order.
 * 8. Perform all assertions from dataset.
 *
 * @group Order_Management_(CS)
 * @ZephyrId MAGETWO-29382
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AssignCustomOrderStatusTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Order Status Index page.
     *
     * @var OrderStatusIndex
     */
    protected $orderStatusIndex;

    /**
     * Order Status Assign page.
     *
     * @var OrderStatusAssign
     */
    protected $orderStatusAssign;

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
     * OrderInjectable Fixture.
     *
     * @var OrderInjectable
     */
    protected $order;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Prepare data.
     *
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Inject pages.
     *
     * @param OrderStatusIndex $orderStatusIndex
     * @param OrderStatusAssign $orderStatusAssign
     * @param OrderIndex $orderIndex
     * @return void
     */
    public function __inject(
        OrderStatusIndex $orderStatusIndex,
        OrderStatusAssign $orderStatusAssign,
        OrderIndex $orderIndex
    ) {
        $this->orderStatusIndex = $orderStatusIndex;
        $this->orderStatusAssign = $orderStatusAssign;
        $this->orderIndex = $orderIndex;
    }

    /**
     * Run Assign Custom OrderStatus.
     *
     * @param OrderStatus $orderStatus
     * @param OrderInjectable $order
     * @param array $orderStatusState
     * @param AssertOrderStatusSuccessAssignMessage $assertion
     * @return array
     */
    public function test(
        OrderStatus $orderStatus,
        OrderInjectable $order,
        array $orderStatusState,
        AssertOrderStatusSuccessAssignMessage $assertion
    ) {
        // Preconditions:
        $orderStatus->persist();
        /** @var OrderStatus $orderStatus */
        $orderStatus = $this->fixtureFactory->createByCode(
            'orderStatus',
            ['data' => array_merge($orderStatus->getData(), $orderStatusState)]
        );
        $this->orderStatus = $orderStatus;

        // Steps:
        $this->orderStatusIndex->open();
        $this->orderStatusIndex->getGridPageActions()->assignStatusToState();
        $this->orderStatusAssign->getAssignForm()->fill($orderStatus);
        $this->orderStatusAssign->getPageActionsBlock()->save();
        $assertion->processAssert($this->orderStatusIndex);

        // Prepare data for constraints
        $config = $this->fixtureFactory->createByCode('configData', [
            'dataset' => 'checkmo_custom_new_order_status',
            'data' => ['payment/checkmo/order_status' => ['value' => $orderStatus->getStatus()]]
        ]);
        $config->persist();
        $order->persist();
        $this->order = $order;

        return [
            'orderId' => $order->getId(),
            'customer' => $order->getDataFieldConfig('customer_id')['source']->getCustomer(),
            'status' => $orderStatus->getLabel()
        ];
    }

    /**
     * Change created order status and unassign custom order status.
     *
     * @return void
     */
    public function tearDown()
    {
        if ($this->order) {
            $this->orderIndex->open()->getSalesOrderGrid()->massaction([['id' => $this->order->getId()]], 'Cancel');
        }
        if ($this->orderStatus) {
            $filter = ['label' => $this->orderStatus->getLabel()];
            $this->orderStatusIndex->open()->getOrderStatusGrid()->searchAndUnassign($filter);
            $this->orderStatusIndex->getMessagesBlock()->waitSuccessMessage();
            $this->objectManager->create(
                'Magento\Config\Test\TestStep\SetupConfigurationStep',
                ['configData' => 'checkmo_custom_new_order_status_rollback']
            )->run();
        }
    }
}
