<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorizenet\Test\TestStep;

use Magento\Authorizenet\Test\Fixture\SandboxCustomer;
use Magento\Authorizenet\Test\Page\Sandbox\Main;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Sales\Test\Constraint\AssertInvoiceStatusInOrdersGrid;
use Magento\Sales\Test\Constraint\AssertOrderButtonsAvailable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;

/**
 * Accept transaction on Authorize.Net sandbox.
 */
class AcceptTransactionOnAuthorizenetStep implements TestStepInterface
{
    /**
     * Authorize.Net Sandbox customer fixture.
     *
     * @var SandboxCustomer
     */
    private $sandboxCustomer;

    /**
     * Authorize.Net Sandbox account main page.
     *
     * @var Main
     */
    private $main;

    /**
     * Sales Order View page.
     *
     * @var SalesOrderView
     */
    private $salesOrderView;

    /**
     * Order Index page.
     *
     * @var OrderIndex
     */
    private $salesOrder;

    /**
     * Order id.
     *
     * @var string
     */
    private $orderId;

    /**
     * Assert invoice status on order page in Admin.
     *
     * @var AssertInvoiceStatusInOrdersGrid
     */
    private $assertInvoiceStatusInOrdersGrid;

    /**
     * Unsettled order data.
     *
     * @var array
     */
    private $orderBeforeAccept;

    /**
     * Assert that specified in data set buttons exist on order page in Admin.
     *
     * @var AssertOrderButtonsAvailable
     */
    private $assertOrderButtonsAvailable;

    /**
     * @param SandboxCustomer $sandboxCustomer
     * @param Main $main
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param AssertInvoiceStatusInOrdersGrid $assertInvoiceStatusInOrdersGrid
     * @param AssertOrderButtonsAvailable $assertOrderButtonsAvailable
     * @param array $orderBeforeAccept
     * @param string $orderId
     */
    public function __construct(
        SandboxCustomer $sandboxCustomer,
        Main $main,
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        AssertInvoiceStatusInOrdersGrid $assertInvoiceStatusInOrdersGrid,
        AssertOrderButtonsAvailable $assertOrderButtonsAvailable,
        array $orderBeforeAccept,
        $orderId
    ) {
        $this->sandboxCustomer = $sandboxCustomer;
        $this->main = $main;
        $this->salesOrderView = $salesOrderView;
        $this->salesOrder = $salesOrder;
        $this->assertInvoiceStatusInOrdersGrid = $assertInvoiceStatusInOrdersGrid;
        $this->assertOrderButtonsAvailable = $assertOrderButtonsAvailable;
        $this->orderBeforeAccept = $orderBeforeAccept;
        $this->orderId = $orderId;
    }

    /**
     * Accept transaction on sandbox.authorize.net account.
     *
     * @return void
     */
    public function run()
    {
        $this->assertInvoiceStatusInOrdersGrid->processAssert(
            $this->salesOrderView,
            $this->orderBeforeAccept['invoiceStatus'],
            $this->orderId
        );
        $this->assertOrderButtonsAvailable->processAssert(
            $this->salesOrderView,
            $this->orderBeforeAccept['buttonsAvailable']
        );
        $this->salesOrder->open();
        $this->salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $this->orderId]);

        /** @var \Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info $infoTab */
        $infoTab = $this->salesOrderView->getOrderForm()->openTab('info')->getTab('info');
        $latestComment = $infoTab->getCommentsHistoryBlock()->getLatestComment();
        preg_match('/"(\d+)"/', $latestComment['comment'], $matches);
        $transactionId = $matches[1];
        $this->main->open();
        $this->main->getLoginBlock()->fill($this->sandboxCustomer);
        $this->main->getLoginBlock()->sandboxLogin();
        $this->main->getMenuBlock()->acceptNotification();
        $this->main->getMenuBlock()->openSearchMenu();
        $this->main->getSearchFormBlock()->search();
        $this->main->getTransactionsGridBlock()->openTransaction($transactionId);
        $this->main->getTransactionsGridBlock()->approveTransaction();
        $this->main->getTransactionsGridBlock()->confirmTransactionApproval();
    }
}
