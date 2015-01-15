<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Order data convert model
 */
namespace Magento\Sales\Model\Convert;

class Order extends \Magento\Framework\Object
{
    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Sales\Model\QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var \Magento\Sales\Model\Quote\AddressFactory
     */
    protected $_quoteAddressFactory;

    /**
     * @var \Magento\Sales\Model\Quote\PaymentFactory
     */
    protected $_quotePaymentFactory;

    /**
     * @var \Magento\Sales\Model\Quote\ItemFactory
     */
    protected $_quoteItemFactory;

    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $_orderInvoiceFactory;

    /**
     * @var \Magento\Sales\Model\Order\Invoice\ItemFactory
     */
    protected $_invoiceItemFactory;

    /**
     * @var \Magento\Sales\Model\Order\ShipmentFactory
     */
    protected $_orderShipmentFactory;

    /**
     * @var \Magento\Sales\Model\Order\CreditmemoFactory
     */
    protected $_creditmemoFactory;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo\ItemFactory
     */
    protected $_creditmemoItemFactory;

    /**
     * @var \Magento\Framework\Object\Copy
     */
    protected $_objectCopyService;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Sales\Model\QuoteFactory $quoteFactory
     * @param \Magento\Sales\Model\Quote\AddressFactory $quoteAddressFactory
     * @param \Magento\Sales\Model\Quote\PaymentFactory $quotePaymentFactory
     * @param \Magento\Sales\Model\Quote\ItemFactory $quoteItemFactory
     * @param \Magento\Sales\Model\Order\InvoiceFactory $orderInvoiceFactory
     * @param \Magento\Sales\Model\Order\Invoice\ItemFactory $invoiceItemFactory
     * @param \Magento\Sales\Model\Order\ShipmentFactory $orderShipmentFactory
     * @param \Magento\Sales\Model\Order\Shipment\ItemFactory $shipmentItemFactory
     * @param \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory
     * @param \Magento\Sales\Model\Order\Creditmemo\ItemFactory $creditmemoItemFactory
     * @param \Magento\Framework\Object\Copy $objectCopyService
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Sales\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Model\Quote\AddressFactory $quoteAddressFactory,
        \Magento\Sales\Model\Quote\PaymentFactory $quotePaymentFactory,
        \Magento\Sales\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Sales\Model\Order\InvoiceFactory $orderInvoiceFactory,
        \Magento\Sales\Model\Order\Invoice\ItemFactory $invoiceItemFactory,
        \Magento\Sales\Model\Order\ShipmentFactory $orderShipmentFactory,
        \Magento\Sales\Model\Order\Shipment\ItemFactory $shipmentItemFactory,
        \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Model\Order\Creditmemo\ItemFactory $creditmemoItemFactory,
        \Magento\Framework\Object\Copy $objectCopyService,
        array $data = []
    ) {
        $this->_eventManager = $eventManager;
        $this->_quoteFactory = $quoteFactory;
        $this->_quoteAddressFactory = $quoteAddressFactory;
        $this->_quotePaymentFactory = $quotePaymentFactory;
        $this->_quoteItemFactory = $quoteItemFactory;
        $this->_orderInvoiceFactory = $orderInvoiceFactory;
        $this->_invoiceItemFactory = $invoiceItemFactory;
        $this->_orderShipmentFactory = $orderShipmentFactory;
        $this->_shipmentItemFactory = $shipmentItemFactory;
        $this->_creditmemoFactory = $creditmemoFactory;
        $this->_creditmemoItemFactory = $creditmemoItemFactory;
        $this->_objectCopyService = $objectCopyService;
        parent::__construct($data);
    }

    /**
     * Converting order object to quote object
     *
     * @param \Magento\Sales\Model\Order $order
     * @param null|\Magento\Sales\Model\Quote $quote
     * @return \Magento\Sales\Model\Quote
     */
    public function toQuote(\Magento\Sales\Model\Order $order, $quote = null)
    {
        if (!$quote instanceof \Magento\Sales\Model\Quote) {
            $quote = $this->_quoteFactory->create();
        }

        $quote->setStoreId($order->getStoreId())->setOrderId($order->getId());

        $this->_objectCopyService->copyFieldsetToTarget('sales_convert_order', 'to_quote', $order, $quote);

        $this->_eventManager->dispatch('sales_convert_order_to_quote', ['order' => $order, 'quote' => $quote]);
        return $quote;
    }

    /**
     * Convert order to shipping address
     *
     * @param   \Magento\Sales\Model\Order $order
     * @return  \Magento\Sales\Model\Quote\Address
     */
    public function toQuoteShippingAddress(\Magento\Sales\Model\Order $order)
    {
        $address = $this->addressToQuoteAddress($order->getShippingAddress());

        $this->_objectCopyService->copyFieldsetToTarget('sales_convert_order', 'to_quote_address', $order, $address);
        return $address;
    }

    /**
     * Convert order address to quote address
     *
     * @param   \Magento\Sales\Model\Order\Address $address
     * @return  \Magento\Sales\Model\Quote\Address
     */
    public function addressToQuoteAddress(\Magento\Sales\Model\Order\Address $address)
    {
        $quoteAddress = $this->_quoteAddressFactory->create()->setStoreId(
            $address->getStoreId()
        )->setAddressType(
            $address->getAddressType()
        )->setCustomerId(
            $address->getCustomerId()
        )->setCustomerAddressId(
            $address->getCustomerAddressId()
        );

        $this->_objectCopyService->copyFieldsetToTarget(
            'sales_convert_order_address',
            'to_quote_address',
            $address,
            $quoteAddress
        );
        return $quoteAddress;
    }

    /**
     * Convert order payment to quote payment
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @param null|\Magento\Sales\Model\Quote\Payment $quotePayment
     * @return \Magento\Sales\Model\Quote\Payment
     */
    public function paymentToQuotePayment(\Magento\Sales\Model\Order\Payment $payment, $quotePayment = null)
    {
        if (!$quotePayment instanceof \Magento\Sales\Model\Quote\Payment) {
            $quotePayment = $this->_quotePaymentFactory->create();
        }

        $quotePayment->setStoreId($payment->getStoreId())->setCustomerPaymentId($payment->getCustomerPaymentId());

        $this->_objectCopyService->copyFieldsetToTarget(
            'sales_convert_order_payment',
            'to_quote_payment',
            $payment,
            $quotePayment
        );
        return $quotePayment;
    }

    /**
     * Retrieve
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return \Magento\Sales\Model\Quote\Item
     */
    public function itemToQuoteItem(\Magento\Sales\Model\Order\Item $item)
    {
        $quoteItem = $this->_quoteItemFactory->create()->setStoreId(
            $item->getOrder()->getStoreId()
        )->setQuoteItemId(
            $item->getId()
        )->setProductId(
            $item->getProductId()
        )->setParentProductId(
            $item->getParentProductId()
        );

        $this->_objectCopyService->copyFieldsetToTarget(
            'sales_convert_order_item',
            'to_quote_item',
            $item,
            $quoteItem
        );
        return $quoteItem;
    }

    /**
     * Convert order object to invoice
     *
     * @param   \Magento\Sales\Model\Order $order
     * @return  \Magento\Sales\Model\Order\Invoice
     */
    public function toInvoice(\Magento\Sales\Model\Order $order)
    {
        $invoice = $this->_orderInvoiceFactory->create();
        $invoice->setOrder(
            $order
        )->setStoreId(
            $order->getStoreId()
        )->setCustomerId(
            $order->getCustomerId()
        )->setBillingAddressId(
            $order->getBillingAddressId()
        )->setShippingAddressId(
            $order->getShippingAddressId()
        );

        $this->_objectCopyService->copyFieldsetToTarget('sales_convert_order', 'to_invoice', $order, $invoice);
        return $invoice;
    }

    /**
     * Convert order item object to invoice item
     *
     * @param   \Magento\Sales\Model\Order\Item $item
     * @return  \Magento\Sales\Model\Order\Invoice\Item
     */
    public function itemToInvoiceItem(\Magento\Sales\Model\Order\Item $item)
    {
        $invoiceItem = $this->_invoiceItemFactory->create();
        $invoiceItem->setOrderItem($item)->setProductId($item->getProductId());

        $this->_objectCopyService->copyFieldsetToTarget(
            'sales_convert_order_item',
            'to_invoice_item',
            $item,
            $invoiceItem
        );
        return $invoiceItem;
    }

    /**
     * Convert order object to Shipment
     *
     * @param   \Magento\Sales\Model\Order $order
     * @return  \Magento\Sales\Model\Order\Shipment
     */
    public function toShipment(\Magento\Sales\Model\Order $order)
    {
        $shipment = $this->_orderShipmentFactory->create();
        $shipment->setOrder(
            $order
        )->setStoreId(
            $order->getStoreId()
        )->setCustomerId(
            $order->getCustomerId()
        )->setBillingAddressId(
            $order->getBillingAddressId()
        )->setShippingAddressId(
            $order->getShippingAddressId()
        );

        $this->_objectCopyService->copyFieldsetToTarget('sales_convert_order', 'to_shipment', $order, $shipment);
        return $shipment;
    }

    /**
     * Convert order item object to Shipment item
     *
     * @param   \Magento\Sales\Model\Order\Item $item
     * @return  \Magento\Sales\Model\Order\Shipment\Item
     */
    public function itemToShipmentItem(\Magento\Sales\Model\Order\Item $item)
    {
        $shipmentItem = $this->_shipmentItemFactory->create();
        $shipmentItem->setOrderItem($item)->setProductId($item->getProductId());

        $this->_objectCopyService->copyFieldsetToTarget(
            'sales_convert_order_item',
            'to_shipment_item',
            $item,
            $shipmentItem
        );
        return $shipmentItem;
    }

    /**
     * Convert order object to creditmemo
     *
     * @param   \Magento\Sales\Model\Order $order
     * @return  \Magento\Sales\Model\Order\Creditmemo
     */
    public function toCreditmemo(\Magento\Sales\Model\Order $order)
    {
        $creditmemo = $this->_creditmemoFactory->create();
        $creditmemo->setOrder(
            $order
        )->setStoreId(
            $order->getStoreId()
        )->setCustomerId(
            $order->getCustomerId()
        )->setBillingAddressId(
            $order->getBillingAddressId()
        )->setShippingAddressId(
            $order->getShippingAddressId()
        );

        $this->_objectCopyService->copyFieldsetToTarget('sales_convert_order', 'to_cm', $order, $creditmemo);
        return $creditmemo;
    }

    /**
     * Convert order item object to Creditmemo item
     *
     * @param   \Magento\Sales\Model\Order\Item $item
     * @return  \Magento\Sales\Model\Order\Creditmemo\Item
     */
    public function itemToCreditmemoItem(\Magento\Sales\Model\Order\Item $item)
    {
        $creditmemoItem = $this->_creditmemoItemFactory->create();
        $creditmemoItem->setOrderItem($item)->setProductId($item->getProductId());

        $this->_objectCopyService->copyFieldsetToTarget(
            'sales_convert_order_item',
            'to_cm_item',
            $item,
            $creditmemoItem
        );
        return $creditmemoItem;
    }
}
