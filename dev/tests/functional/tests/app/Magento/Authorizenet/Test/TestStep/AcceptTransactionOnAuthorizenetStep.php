<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorizenet\Test\TestStep;

use Magento\Authorizenet\Test\Fixture\SandboxCustomer;
use Magento\Authorizenet\Test\Fixture\TransactionSearch;
use Magento\Authorizenet\Test\Page\Sandbox\Main;
use Magento\Mtf\Client\BrowserInterface;
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
     * Client Browser instance.
     *
     * @var BrowserInterface
     */
    private $browser;

    /**
     * Form frame selector.
     *
     * @var string
     */
    private $frame = 'frameset > frame';

    /**
     * Transaction search fixture.
     *
     * @var TransactionSearch
     */
    private $transactionSearch;

    /**
     * @param SandboxCustomer $sandboxCustomer
     * @param TransactionSearch $transactionSearch
     * @param Main $main
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param AssertInvoiceStatusInOrdersGrid $assertInvoiceStatusInOrdersGrid
     * @param AssertOrderButtonsAvailable $assertOrderButtonsAvailable
     * @param BrowserInterface $browser
     * @param array $orderBeforeAccept
     * @param string $orderId
     */
    public function __construct(
        SandboxCustomer $sandboxCustomer,
        TransactionSearch $transactionSearch,
        Main $main,
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        AssertInvoiceStatusInOrdersGrid $assertInvoiceStatusInOrdersGrid,
        AssertOrderButtonsAvailable $assertOrderButtonsAvailable,
        BrowserInterface $browser,
        array $orderBeforeAccept,
        $orderId
    ) {
        $this->sandboxCustomer = $sandboxCustomer;
        $this->transactionSearch = $transactionSearch;
        $this->main = $main;
        $this->salesOrderView = $salesOrderView;
        $this->salesOrder = $salesOrder;
        $this->assertInvoiceStatusInOrdersGrid = $assertInvoiceStatusInOrdersGrid;
        $this->assertOrderButtonsAvailable = $assertOrderButtonsAvailable;
        $this->browser = $browser;
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
        if (!preg_match('/"(\d+)"/', $latestComment['comment'], $matches)) {
            throw new \Exception('Comment with transaction id cannot be found.');
        }
        $transactionId = $matches[1];
        $this->main->open();
        $this->browser->switchToFrame($this->browser->find($this->frame)->getLocator());
        $this->main->getLoginBlock()->fill($this->sandboxCustomer)->login();
        $this->main->getMenuBlock()->acceptNotification()->openSearchMenu();
        $this->main->getSearchFormBlock()->fill($this->transactionSearch)->search();
        $this->main->getTransactionsGridBlock()->openTransaction($transactionId)->approveTransaction();
    }
}
