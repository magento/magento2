<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\TestStep;

use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderCreditMemoNew;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\OrderInvoiceView;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;

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
     * @construct
     * @param OrderIndex $orderIndex
     * @param SalesOrderView $salesOrderView
     * @param OrderInjectable $order
     * @param OrderInvoiceView $orderInvoiceView
     * @param OrderCreditMemoNew $orderCreditMemoNew
     * @param array|null refundData [optional]
     */
    public function __construct(
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
        /** @var \Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Invoices\Grid $invoicesGrid */
        $invoicesGrid = $this->salesOrderView->getOrderForm()->getTab('invoices')->getGridBlock();
        $this->salesOrderView->getOrderForm()->openTab('invoices');
        $invoicesGrid->viewInvoice();
        $this->salesOrderView->getPageActions()->orderInvoiceCreditMemo();
        if (!empty($this->refundData)) {
            $this->orderCreditMemoNew->getFormBlock()->fillProductData(
                $this->refundData,
                $this->order->getEntityId()['products']
            );
            $this->orderCreditMemoNew->getFormBlock()->updateQty();
        }
        $this->orderCreditMemoNew->getFormBlock()->submit();

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
