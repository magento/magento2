<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Checkout\Test\Fixture\Cart;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderCreditMemoNew;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\OrderInvoiceView;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;

/**
 * Create credit memo for order placed using online payment methods.
 */
class CreateOnlineCreditMemoStep implements TestStepInterface
{
    /**
     * Orders Page.
     *
     * @var OrderIndex
     */
    private $orderIndex;

    /**
     * Order View Page.
     *
     * @var SalesOrderView
     */
    private $salesOrderView;

    /**
     * OrderCreditMemoNew Page.
     *
     * @var OrderCreditMemoNew
     */
    private $orderCreditMemoNew;

    /**
     * OrderInjectable fixture.
     *
     * @var OrderInjectable
     */
    private $order;

    /**
     * Credit memo data.
     *
     * @var array|null
     */
    private $refundData;

    /**
     * Order invoice view page.
     *
     * @var OrderInvoiceView
     */
    private $orderInvoiceView;
    /**
     * @var Cart
     */
    private $cart;

    /**
     * @construct
     * @param Cart $cart
     * @param OrderIndex $orderIndex
     * @param SalesOrderView $salesOrderView
     * @param OrderInjectable $order
     * @param OrderInvoiceView $orderInvoiceView
     * @param OrderCreditMemoNew $orderCreditMemoNew
     * @param array|null refundData [optional]
     */
    public function __construct(
        Cart $cart,
        OrderIndex $orderIndex,
        SalesOrderView $salesOrderView,
        OrderInjectable $order,
        OrderInvoiceView $orderInvoiceView,
        OrderCreditMemoNew $orderCreditMemoNew,
        $refundData = null
    ) {
        $this->orderIndex = $orderIndex;
        $this->salesOrderView = $salesOrderView;
        $this->order = $order;
        $this->orderCreditMemoNew = $orderCreditMemoNew;
        $this->refundData = $refundData;
        $this->orderInvoiceView = $orderInvoiceView;
        $this->cart = $cart;
    }

    /**
     * Create credit memo.
     *
     * @return array
     */
    public function run()
    {
        $this->orderIndex->open();
        $this->orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $this->order->getId()]);
        $refundsData = $this->order->getRefund();
        foreach ($refundsData as $refundData) {
            /** @var \Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Invoices\Grid $invoicesGrid */
            $invoicesGrid = $this->salesOrderView->getOrderForm()->getTab('invoices')->getGridBlock();
            $this->salesOrderView->getOrderForm()->openTab('invoices');
            $invoicesGrid->viewInvoice();
            $this->salesOrderView->getPageActions()->orderInvoiceCreditMemo();
            $this->orderCreditMemoNew->getFormBlock()->fillProductData(
                $refundData,
                $this->cart->getItems()
            );
            $this->orderCreditMemoNew->getFormBlock()->updateQty();
            $this->orderCreditMemoNew->getFormBlock()->submit();
        }

        return ['ids' => ['creditMemoIds' => $this->getCreditMemoIds()]];
    }

    /**
     * Get credit memo ids.
     *
     * @return array
     */
    private function getCreditMemoIds()
    {
        $this->salesOrderView->getOrderForm()->openTab('creditmemos');
        return $this->salesOrderView->getOrderForm()->getTab('creditmemos')->getGridBlock()->getIds();
    }
}
