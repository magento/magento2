<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Order data convert model
 */
namespace Magento\Sales\Model\Convert;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Order extends \Magento\Framework\DataObject
{
    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var \Magento\Sales\Model\Order\Invoice\ItemFactory
     */
    protected $_invoiceItemFactory;

    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo\ItemFactory
     */
    protected $_creditmemoItemFactory;

    /**
     * @var \Magento\Framework\DataObject\Copy
     */
    protected $_objectCopyService;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Sales\Model\Order\Invoice\ItemFactory $invoiceItemFactory
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param \Magento\Sales\Model\Order\Shipment\ItemFactory $shipmentItemFactory
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository
     * @param \Magento\Sales\Model\Order\Creditmemo\ItemFactory $creditmemoItemFactory
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Model\Order\Invoice\ItemFactory $invoiceItemFactory,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Model\Order\Shipment\ItemFactory $shipmentItemFactory,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Sales\Model\Order\Creditmemo\ItemFactory $creditmemoItemFactory,
        \Magento\Framework\DataObject\Copy $objectCopyService,
        array $data = []
    ) {
        $this->_eventManager = $eventManager;
        $this->invoiceRepository = $invoiceRepository;
        $this->_invoiceItemFactory = $invoiceItemFactory;
        $this->shipmentRepository = $shipmentRepository;
        $this->_shipmentItemFactory = $shipmentItemFactory;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->_creditmemoItemFactory = $creditmemoItemFactory;
        $this->_objectCopyService = $objectCopyService;
        parent::__construct($data);
    }

    /**
     * Convert order object to invoice
     *
     * @param   \Magento\Sales\Model\Order $order
     * @return  \Magento\Sales\Model\Order\Invoice
     */
    public function toInvoice(\Magento\Sales\Model\Order $order)
    {
        $invoice = $this->invoiceRepository->create();
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
        $shipment = $this->shipmentRepository->create();
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
        $creditmemo = $this->creditmemoRepository->create();
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
