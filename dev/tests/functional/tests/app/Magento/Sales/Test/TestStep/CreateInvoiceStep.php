<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Checkout\Test\Fixture\Cart;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\OrderInvoiceNew;
use Magento\Sales\Test\Page\Adminhtml\OrderInvoiceView;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\TestStep\Utils\CompareQtyTrait;
use Magento\Shipping\Test\Page\Adminhtml\OrderShipmentView;

/**
 * Create invoice from order on backend.
 */
class CreateInvoiceStep implements TestStepInterface
{
    use CompareQtyTrait;

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
     * Order New Invoice Page.
     *
     * @var OrderInvoiceNew
     */
    private $orderInvoiceNew;

    /**
     * Order invoice view page.
     *
     * @var OrderInvoiceView
     */
    private $orderInvoiceView;

    /**
     * Order shipment view page.
     *
     * @var OrderShipmentView
     */
    private $orderShipmentView;

    /**
     * OrderInjectable fixture.
     *
     * @var OrderInjectable
     */
    private $order;

    /**
     * Payment Action.
     *
     * @var string
     */
    private $paymentAction;

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
     * @param OrderInvoiceNew $orderInvoiceNew
     * @param OrderInvoiceView $orderInvoiceView
     * @param OrderInjectable $order
     * @param OrderShipmentView $orderShipmentView
     * @param string $paymentAction
     */
    public function __construct(
        Cart $cart,
        OrderIndex $orderIndex,
        SalesOrderView $salesOrderView,
        OrderInvoiceNew $orderInvoiceNew,
        OrderInvoiceView $orderInvoiceView,
        OrderInjectable $order,
        OrderShipmentView $orderShipmentView,
        $paymentAction = 'authorize'
    ) {
        $this->cart = $cart;
        $this->orderIndex = $orderIndex;
        $this->salesOrderView = $salesOrderView;
        $this->orderInvoiceNew = $orderInvoiceNew;
        $this->orderInvoiceView = $orderInvoiceView;
        $this->order = $order;
        $this->orderShipmentView = $orderShipmentView;
        $this->paymentAction = $paymentAction;
    }

    /**
     * Create invoice (with shipment optionally) for order in Admin.
     *
     * @return array
     */
    public function run()
    {
        if ($this->paymentAction == 'sale') {
            return null;
        }
        $this->orderIndex->open();
        $this->orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $this->order->getId()]);
        $invoicesData = $this->order->getInvoice() !== null ? $this->order->getInvoice() : ['invoiceData' => []];
        foreach ($invoicesData as $invoiceData) {
            $this->salesOrderView->getPageActions()->invoice();

            $items = $this->getItems();
            $this->orderInvoiceNew->getFormBlock()->fillProductData($invoiceData, $items);
            if ($this->compare($this->cart->getItems(), $invoiceData)) {
                $this->orderInvoiceNew->getFormBlock()->updateQty();
            }

            $this->orderInvoiceNew->getFormBlock()->fillFormData($invoiceData);
            $this->orderInvoiceNew->getFormBlock()->submit();
            $shipmentIds = $this->getShipmentIds();
        }
        $invoiceIds = $this->getInvoiceIds();

        return [
            'ids' => [
                'invoiceIds' => $invoiceIds,
                'shipmentIds' => isset($shipmentIds) ? $shipmentIds : null,
            ]
        ];
    }

    /**
     * Get invoice ids.
     *
     * @return array
     */
    protected function getInvoiceIds()
    {
        $this->salesOrderView->getOrderForm()->openTab('invoices');
        return $this->salesOrderView->getOrderForm()->getTab('invoices')->getGridBlock()->getIds();
    }

    /**
     * Get shipment ids.
     *
     * @return array
     */
    protected function getShipmentIds()
    {
        $this->salesOrderView->getOrderForm()->openTab('shipments');
        return $this->salesOrderView->getOrderForm()->getTab('shipments')->getGridBlock()->getIds();
    }

    /**
     * Get cart items
     *
     * @return mixed
     */
    protected function getItems()
    {
        return $this->cart->getItems();
    }
}
