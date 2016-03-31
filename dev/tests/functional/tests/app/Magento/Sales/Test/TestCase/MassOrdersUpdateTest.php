<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestCase;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Precondition:
 * 1. Create orders.
 *
 * Steps:
 * 1. Navigate to backend.
 * 2. Go to Sales > Orders.
 * 3. Select Mass Action according to dataset.
 * 4. Submit.
 * 5. Perform Asserts.
 *
 * @group Order_Management_(CS)
 * @ZephyrId MAGETWO-27897
 */
class MassOrdersUpdateTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Order index page.
     *
     * @var OrderIndex
     */
    protected $orderIndex;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Injection data.
     *
     * @param OrderIndex $orderIndex
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(OrderIndex $orderIndex, FixtureFactory $fixtureFactory)
    {
        $this->orderIndex = $orderIndex;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Mass orders update.
     *
     * @param string $steps
     * @param int $ordersCount
     * @param string $action
     * @param string $resultStatuses
     * @return array
     */
    public function test($steps, $ordersCount, $action, $resultStatuses)
    {
        // Preconditions
        $orders = $this->createOrders($ordersCount, $steps);
        $items = $this->prepareFilter($orders);

        // Steps
        $this->orderIndex->open();
        $this->orderIndex->getSalesOrderGrid()->massaction($items, $action);

        return ['orders' => $orders, 'orderStatuses' => explode(',', $resultStatuses)];
    }

    /**
     * Create orders.
     *
     * @param int $count
     * @param string $steps
     * @return array
     */
    protected function createOrders($count, $steps)
    {
        $orders = [];
        $steps = explode('|', $steps);
        for ($i = 0; $i < $count; $i++) {
            /** @var OrderInjectable $order */
            $order = $this->fixtureFactory->createByCode('orderInjectable', ['dataset' => 'default']);
            $order->persist();
            $orders[$i] = $order;
            $this->processSteps($order, $steps[$i]);
        }

        return $orders;
    }

    /**
     * Process which step to take for order.
     *
     * @param OrderInjectable $order
     * @param string $steps
     * @return void
     */
    protected function processSteps(OrderInjectable $order, $steps)
    {
        $steps = array_diff(explode(',', $steps), ['-']);
        foreach ($steps as $step) {
            $action = str_replace(' ', '', ucwords($step));
            $methodAction = (($action != 'OnHold') ? 'Create' : '') . $action . 'Step';
            $path = 'Magento\Sales\Test\TestStep';
            $processStep = $this->objectManager->create($path . '\\' . $methodAction, ['order' => $order]);
            $processStep->run();
        }
    }

    /**
     * Prepare filter.
     *
     * @param OrderInjectable[] $orders
     * @return array
     */
    protected function prepareFilter(array $orders)
    {
        $items = [];
        foreach ($orders as $order) {
            $items[] = ['id' => $order->getId()];
        }

        return $items;
    }
}
