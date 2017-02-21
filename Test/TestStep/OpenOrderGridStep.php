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
     * Magento order status assertion.
     *
     * @var AssertOrderStatus
     */
    private $assertOrderStatus;

    /**
     * Case information on Magento backend assertion.
     *
     * @var AssertCaseInfoOnBackend
     */
    private $assertCaseInfo;

    /**
     * Case information on Magento order grid assertion.
     *
     * @var AssertOrdersGrid
     */
    private $assertOrdersGrid;

    /**
     * Magento order status.
     *
     * @var string
     */
    private $orderStatus;

    /**
     * Order View Page.
     *
     * @var SalesOrderView
     */
    private $salesOrderView;

    /**
     * Customized order view page.
     *
     * @var OrderView
     */
    private $orderView;

    /**
     * Array of Signifyd config data.
     *
     * @var array
     */
    private $signifydData;

    /**
     * @param string $status
     * @param OrderInjectable $order
     * @param OrderIndex $orderIndex
     * @param SalesOrderView $salesOrderView
     * @param OrderView $orderView
     * @param AssertOrderStatus $assertOrderStatus
     * @param AssertCaseInfoOnBackend $assertCaseInfo
     * @param AssertOrdersGrid $assertOrdersGrid
     * @param array $signifydData
     */
    public function __construct(
        $status,
        OrderInjectable $order,
        OrderIndex $orderIndex,
        SalesOrderView $salesOrderView,
        OrderView $orderView,
        AssertOrderStatus $assertOrderStatus,
        AssertCaseInfoOnBackend $assertCaseInfo,
        AssertOrdersGrid $assertOrdersGrid,
        array $signifydData
    ) {
        $this->orderStatus = $status;
        $this->assertOrderStatus = $assertOrderStatus;
        $this->assertCaseInfo = $assertCaseInfo;
        $this->assertOrdersGrid = $assertOrdersGrid;
        $this->salesOrderView = $salesOrderView;
        $this->orderView = $orderView;
        $this->orderIndex = $orderIndex;

        parent::__construct($order, $this->orderIndex);
        $this->signifydData = $signifydData;
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
     *
     * @return void
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
     *
     * @return void
     */
    private function checkCaseInfo()
    {
        $this->assertCaseInfo->processAssert(
            $this->orderView,
            $this->order->getId(),
            $this->signifydData
        );
    }

    /**
     * Run assert to check Signifyd Case Disposition status in orders grid.
     *
     * @return void
     */
    private function checkOrdersGrid()
    {
        $this->assertOrdersGrid->processAssert(
            $this->order->getId(),
            $this->orderStatus,
            $this->orderView,
            $this->signifydData
        );
    }
}
