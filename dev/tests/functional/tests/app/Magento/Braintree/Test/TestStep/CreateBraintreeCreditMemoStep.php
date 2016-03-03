<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\TestStep;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderCreditMemoNew;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\OrderInvoiceView;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\TestStep\TestStepInterface;
use Braintree\Gateway;

/**
 * Create credit memo for order placed via Braintree credit card payment method.
 */
class CreateBraintreeCreditMemoStep implements TestStepInterface
{
    /**
     * Orders Page.
     *
     * @var OrderIndex
     */
    protected $orderIndex;

    /**
     * Order View Page.
     *
     * @var SalesOrderView
     */
    protected $salesOrderView;

    /**
     * OrderCreditMemoNew Page.
     *
     * @var OrderCreditMemoNew
     */
    protected $orderCreditMemoNew;

    /**
     * OrderInjectable fixture.
     *
     * @var OrderInjectable
     */
    protected $order;

    /**
     * Credit memo data.
     *
     * @var array|null
     */
    protected $data;

    /**
     * @var Gateway
     */
    private $gateway;

    /**
     * @var OrderInvoiceView
     */
    private $orderInvoiceView;

    /**
     * @construct
     * @param OrderIndex $orderIndex
     * @param SalesOrderView $salesOrderView
     * @param OrderInjectable $order
     * @param OrderInvoiceView $orderInvoiceView
     * @param OrderCreditMemoNew $orderCreditMemoNew
     * @param Gateway $gateway
     * @param array|null $data [optional]
     */
    public function __construct(
        OrderIndex $orderIndex,
        SalesOrderView $salesOrderView,
        OrderInjectable $order,
        OrderInvoiceView $orderInvoiceView,
        OrderCreditMemoNew $orderCreditMemoNew,
        Gateway $gateway,
        $data = null
    ) {
        $this->orderIndex = $orderIndex;
        $this->salesOrderView = $salesOrderView;
        $this->order = $order;
        $this->orderCreditMemoNew = $orderCreditMemoNew;
        $this->data = $data;
        $this->gateway = $gateway;
        $this->orderInvoiceView = $orderInvoiceView;
    }

    /**
     * Create credit memo.
     *
     * @return array
     */
    public function run()
    {
        $transactionId = '';
        $this->orderIndex->open();
        $this->orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $this->order->getId()]);
        $comment = $this->salesOrderView->getOrderHistoryBlock()->getCommentsHistory();
        preg_match('/(ID: ")(\w+-*\w+)(")/', $comment, $matches);
        if (!empty($matches[2])) {
            $transactionId = $matches[2];
        }
        $this->gateway->testing()->settle($transactionId);
        /** @var \Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Invoices\Grid $invoicesGrid */
        $invoicesGrid = $this->salesOrderView->getOrderForm()->getTab('invoices')->getGridBlock();
        foreach ($invoiceIds = $invoicesGrid->getIds() as $invoiceId) {
            $this->salesOrderView->getOrderForm()->openTab('invoices');
            $invoicesGrid->viewInvoice($invoiceId);
            $this->salesOrderView->getPageActions()->orderInvoiceCreditMemo();
            if (!empty($this->data)) {
                $this->orderCreditMemoNew->getFormBlock()->fillProductData(
                    $this->data,
                    $this->order->getEntityId()['products']
                );
                $this->orderCreditMemoNew->getFormBlock()->updateQty();
                $this->orderCreditMemoNew->getFormBlock()->fillFormData($this->data);
            }
            $this->orderCreditMemoNew->getFormBlock()->submit();
        }

        return ['ids' => ['creditMemoIds' => $this->getCreditMemoIds()]];
    }

    /**
     * Get credit memo ids.
     *
     * @return array
     */
    protected function getCreditMemoIds()
    {
        $this->salesOrderView->getOrderForm()->openTab('creditmemos');
        return $this->salesOrderView->getOrderForm()->getTab('creditmemos')->getGridBlock()->getIds();
    }
}
