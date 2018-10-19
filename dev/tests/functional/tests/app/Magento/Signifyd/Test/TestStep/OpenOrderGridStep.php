<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\Constraint\AssertOrderStatusIsCorrect as AssertOrderStatus;
use Magento\Signifyd\Test\Constraint\AssertSignifydCaseInOrdersGrid as AssertOrdersGrid;
use Magento\Signifyd\Test\Constraint\AssertCaseInfoOnAdmin;
use Magento\Signifyd\Test\Fixture\SignifydData;
use Magento\Signifyd\Test\Page\Adminhtml\OrdersGrid;

/**
 * Open order grid step.
 */
class OpenOrderGridStep implements TestStepInterface
{
    /**
     * Magento order status assertion.
     *
     * @var AssertOrderStatus
     */
    private $assertOrderStatus;

    /**
     * Case information on Magento Admin assertion.
     *
     * @var AssertCaseInfoOnAdmin
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
    private $placeOrderStatus;

    /**
     * Magento order id.
     *
     * @var int
     */
    private $orderId;

    /**
     * Order View Page.
     *
     * @var SalesOrderView
     */
    private $salesOrderView;

    /**
     * Orders grid page.
     *
     * @var OrdersGrid
     */
    private $ordersGrid;

    /**
     * Signifyd data fixture.
     *
     * @var array
     */
    private $signifydData;

    /**
     * Orders Page.
     *
     * @var OrderIndex
     */
    private $orderIndex;

    /**
     * @param string $placeOrderStatus
     * @param int $orderId
     * @param OrderIndex $orderIndex
     * @param SalesOrderView $salesOrderView
     * @param OrdersGrid $ordersGrid
     * @param AssertOrderStatus $assertOrderStatus
     * @param AssertCaseInfoOnAdmin $assertCaseInfo
     * @param AssertOrdersGrid $assertOrdersGrid
     * @param SignifydData $signifydData
     */
    public function __construct(
        $placeOrderStatus,
        $orderId,
        OrderIndex $orderIndex,
        SalesOrderView $salesOrderView,
        OrdersGrid $ordersGrid,
        AssertOrderStatus $assertOrderStatus,
        AssertCaseInfoOnAdmin $assertCaseInfo,
        AssertOrdersGrid $assertOrdersGrid,
        SignifydData $signifydData
    ) {
        $this->placeOrderStatus = $placeOrderStatus;
        $this->orderId = $orderId;
        $this->orderIndex = $orderIndex;
        $this->salesOrderView = $salesOrderView;
        $this->ordersGrid = $ordersGrid;
        $this->assertOrderStatus = $assertOrderStatus;
        $this->assertCaseInfo = $assertCaseInfo;
        $this->assertOrdersGrid = $assertOrdersGrid;
        $this->signifydData = $signifydData;
    }

    /**
     * Open order.
     *
     * @return void
     */
    public function run()
    {
        $this->checkOrdersGrid();
        $this->checkCaseInfo();
        $this->checkOrderStatus();
    }

    /**
     * Run assert to check Signifyd Case Disposition status in orders grid.
     *
     * @return void
     */
    private function checkOrdersGrid()
    {
        $this->assertOrdersGrid->processAssert(
            $this->orderId,
            $this->ordersGrid,
            $this->signifydData
        );
    }

    /**
     * Run assert to check order status is valid.
     *
     * @return void
     */
    private function checkOrderStatus()
    {
        $this->assertOrderStatus->processAssert(
            $this->placeOrderStatus,
            $this->orderId,
            $this->orderIndex,
            $this->salesOrderView
        );
    }

    /**
     * Run assert to check Signifyd Case information is correct in Admin.
     *
     * @return void
     */
    private function checkCaseInfo()
    {
        $this->assertCaseInfo->processAssert(
            $this->salesOrderView,
            $this->orderIndex,
            $this->signifydData,
            $this->orderId
        );
    }
}
