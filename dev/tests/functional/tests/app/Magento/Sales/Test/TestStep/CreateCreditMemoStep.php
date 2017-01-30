<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Checkout\Test\Fixture\Cart;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderCreditMemoNew;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Create credit memo from order on backend.
 */
class CreateCreditMemoStep implements TestStepInterface
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
     * Checkout Cart fixture.
     *
     * @var Cart
     */
    private $cart;

    /**
     * @param Cart $cart
     * @param OrderIndex $orderIndex
     * @param SalesOrderView $salesOrderView
     * @param OrderInjectable $order
     * @param OrderCreditMemoNew $orderCreditMemoNew
     */
    public function __construct(
        Cart $cart,
        OrderIndex $orderIndex,
        SalesOrderView $salesOrderView,
        OrderInjectable $order,
        OrderCreditMemoNew $orderCreditMemoNew
    ) {
        $this->cart = $cart;
        $this->orderIndex = $orderIndex;
        $this->salesOrderView = $salesOrderView;
        $this->order = $order;
        $this->orderCreditMemoNew = $orderCreditMemoNew;
    }

    /**
     * Create credit memo from order on backend.
     *
     * @return array
     */
    public function run()
    {
        $this->orderIndex->open();
        $this->orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $this->order->getId()]);
        $refundsData = $this->order->getRefund() !== null ? $this->order->getRefund() : ['refundData' => []];
        foreach ($refundsData as $refundData) {
            $this->salesOrderView->getPageActions()->orderCreditMemo();
            $this->orderCreditMemoNew->getFormBlock()->fillProductData(
                $refundData,
                $this->cart->getItems()
            );
            $this->orderCreditMemoNew->getFormBlock()->updateQty();
            $this->orderCreditMemoNew->getFormBlock()->fillFormData($refundData);
            $this->orderCreditMemoNew->getFormBlock()->submit();
        }

        return [
            'ids' => ['creditMemoIds' => $this->getCreditMemoIds()],
            'customer' => $this->order->getDataFieldConfig('customer_id')['source']->getCustomer()
        ];
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
