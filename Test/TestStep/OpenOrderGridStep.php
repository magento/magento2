<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Signifyd\Test\TestStep;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\TestStep\OpenOrderStep;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\Constraint\AssertOrderStatusIsCorrect as AssertOrderStatus;
use Magento\Signifyd\Test\Constraint\AssertSignifydCaseInOrdersGrid as AssertOrdersGrid;
use Magento\Signifyd\Test\Constraint\AssertCaseInfoOnBackend;
use Magento\Signifyd\Test\Page\Adminhtml\OrderView;

/**
 * Open order grid step.
 */
class OpenOrderGridStep extends OpenOrderStep
{
    /**
     * @var AssertOrderStatus
     */
    private $assertOrderStatus;

    /**
     * @var AssertCaseInfoOnBackend
     */
    private $assertCaseInfo;

    /**
     * @var AssertOrdersGrid
     */
    private $assertOrdersGrid;

    /**
     * @var string
     */
    private $orderStatus;

    /**
     * @var SalesOrderView
     */
    private $salesOrderView;

    /**
     * @var OrderView
     */
    private $orderView;

    /**
     * @param string $status
     * @param OrderInjectable $order
     * @param OrderIndex $orderIndex
     * @param SalesOrderView $salesOrderView
     * @param OrderView $orderView
     * @param AssertOrderStatus $assertOrderStatus
     * @param AssertCaseInfoOnBackend $assertCaseInfo
     * @param AssertOrdersGrid $assertOrdersGrid
     */
    public function __construct(
        $status,
        OrderInjectable $order,
        OrderIndex $orderIndex,
        SalesOrderView $salesOrderView,
        OrderView $orderView,
        AssertOrderStatus $assertOrderStatus,
        AssertCaseInfoOnBackend $assertCaseInfo,
        AssertOrdersGrid $assertOrdersGrid
    ) {
        $this->orderStatus = $status;
        $this->assertOrderStatus = $assertOrderStatus;
        $this->assertCaseInfo = $assertCaseInfo;
        $this->assertOrdersGrid = $assertOrdersGrid;
        $this->salesOrderView = $salesOrderView;
        $this->orderView = $orderView;
        $this->orderIndex = $orderIndex;

        parent::__construct($order, $this->orderIndex);
    }

    /**
     * Open order.
     *
     * @return void
     */
    public function run()
    {
        parent::run();

        $this->checkOrdersGrid();
        $this->checkOrderStatus();
        $this->checkCaseInfo();
    }

    /**
     * Run assert to check order status is valid.
     */
    private function checkOrderStatus()
    {
        $this->assertOrderStatus->processAssert(
            $this->orderStatus,
            $this->order->getId(),
            $this->orderIndex,
            $this->salesOrderView
        );
    }

    /**
     * Run assert to check Signifyd Case information is correct on backend.
     */
    private function checkCaseInfo()
    {
        $this->assertCaseInfo->processAssert(
            $this->orderView,
            $this->order->getId()
        );
    }

    /**
     * Run assert to check Signifyd Case Disposition status in orders grid.
     */
    private function checkOrdersGrid()
    {
        $this->assertOrdersGrid->processAssert(
            $this->order->getId(),
            $this->orderStatus,
            $this->orderView
        );
    }
}
