<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Checkout\Test\Fixture\Cart;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderCreditMemoNew;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\TestStep\Utils\CompareQtyTrait;

/**
 * Create credit memo from order on backend.
 */
class CreateCreditMemoStep implements TestStepInterface
{
    use CompareQtyTrait;

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

            $items = $this->cart->getItems();
            $this->orderCreditMemoNew->getFormBlock()->fillProductData($refundData, $items);
            if ($this->compare($items, $refundData)) {
                $this->orderCreditMemoNew->getFormBlock()->updateQty();
            }

            $this->orderCreditMemoNew->getFormBlock()->fillFormData($refundData);
            $this->orderCreditMemoNew->getTotalsBlock()->clickUpdateTotals();
            
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
