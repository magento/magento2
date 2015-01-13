<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Convert;

/**
 * Quote data convert model
 */
class Quote extends \Magento\Framework\Object
{
    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Sales\Model\Order\AddressFactory
     */
    protected $_orderAddressFactory;

    /**
     * @var \Magento\Sales\Model\Order\PaymentFactory
     */
    protected $_orderPaymentFactory;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $_orderItemFactory;

    /**
     * @var \Magento\Framework\Object\Copy
     */
    private $_objectCopyService;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\Order\AddressFactory $orderAddressFactory
     * @param \Magento\Sales\Model\Order\PaymentFactory $orderPaymentFactory
     * @param \Magento\Sales\Model\Order\ItemFactory $orderItemFactory
     * @param \Magento\Framework\Object\Copy $objectCopyService
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\AddressFactory $orderAddressFactory,
        \Magento\Sales\Model\Order\PaymentFactory $orderPaymentFactory,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Framework\Object\Copy $objectCopyService,
        array $data = []
    ) {
        $this->_eventManager = $eventManager;
        $this->_orderFactory = $orderFactory;
        $this->_orderAddressFactory = $orderAddressFactory;
        $this->_orderPaymentFactory = $orderPaymentFactory;
        $this->_orderItemFactory = $orderItemFactory;
        $this->_objectCopyService = $objectCopyService;
        parent::__construct($data);
    }

    /**
     * Convert quote model to order model
     *
     * @param \Magento\Sales\Model\Quote $quote
     * @param null|\Magento\Sales\Model\Order $order
     * @return \Magento\Sales\Model\Order
     */
    public function toOrder(\Magento\Sales\Model\Quote $quote, $order = null)
    {
        if (!$order instanceof \Magento\Sales\Model\Order) {
            $order = $this->_orderFactory->create();
        }
        /* @var $order \Magento\Sales\Model\Order */
        $order->setIncrementId($quote->getReservedOrderId())
            ->setStoreId($quote->getStoreId())
            ->setQuoteId($quote->getId())
            ->setQuote($quote)
            ->setCustomer($quote->getCustomer());

        $this->_objectCopyService->copyFieldsetToTarget('sales_convert_quote', 'to_order', $quote, $order);
        $this->_eventManager->dispatch('sales_convert_quote_to_order', ['order' => $order, 'quote' => $quote]);
        return $order;
    }

    /**
     * Convert quote address model to order
     *
     * @param \Magento\Sales\Model\Quote\Address $address
     * @param null|\Magento\Sales\Model\Order $order
     * @return  \Magento\Sales\Model\Order
     */
    public function addressToOrder(\Magento\Sales\Model\Quote\Address $address, $order = null)
    {
        if (!$order instanceof \Magento\Sales\Model\Order) {
            $order = $this->toOrder($address->getQuote());
        }

        $this->_objectCopyService->copyFieldsetToTarget('sales_convert_quote_address', 'to_order', $address, $order);

        $this->_eventManager->dispatch(
            'sales_convert_quote_address_to_order',
            ['address' => $address, 'order' => $order]
        );
        return $order;
    }

    /**
     * Convert quote address to order address
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @return  \Magento\Sales\Model\Order\Address
     */
    public function addressToOrderAddress(\Magento\Sales\Model\Quote\Address $address)
    {
        $orderAddress = $this->_orderAddressFactory->create()
            ->setStoreId($address->getStoreId())
            ->setAddressType($address->getAddressType())
            ->setCustomerId($address->getCustomerId())
            ->setCustomerAddressId($address->getCustomerAddressId());

        $this->_objectCopyService->copyFieldsetToTarget(
            'sales_convert_quote_address',
            'to_order_address',
            $address,
            $orderAddress
        );

        $this->_eventManager->dispatch(
            'sales_convert_quote_address_to_order_address',
            ['address' => $address, 'order_address' => $orderAddress]
        );

        return $orderAddress;
    }

    /**
     * Convert quote payment to order payment
     *
     * @param   \Magento\Sales\Model\Quote\Payment $payment
     * @return  \Magento\Sales\Model\Quote\Payment
     */
    public function paymentToOrderPayment(\Magento\Sales\Model\Quote\Payment $payment)
    {
        /** @var \Magento\Sales\Model\Order\Payment $orderPayment */
        $orderPayment = $this->_orderPaymentFactory->create()->setStoreId($payment->getStoreId());
        $orderPayment->setCustomerPaymentId($payment->getCustomerPaymentId());

        $this->_objectCopyService->copyFieldsetToTarget(
            'sales_convert_quote_payment',
            'to_order_payment',
            $payment,
            $orderPayment
        );
        $orderPayment->setAdditionalInformation(
            \Magento\Payment\Model\Method\Substitution::INFO_KEY_TITLE,
            $payment->getMethodInstance()->getTitle()
        );

        return $orderPayment;
    }

    /**
     * Convert quote item to order item
     *
     * @param   \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return  \Magento\Sales\Model\Order\Item
     */
    public function itemToOrderItem(\Magento\Sales\Model\Quote\Item\AbstractItem $item)
    {
        $orderItem = $this->_orderItemFactory->create()
            ->setStoreId($item->getStoreId())
            ->setQuoteItemId($item->getId())
            ->setQuoteParentItemId($item->getParentItemId())
            ->setProductId($item->getProductId())
            ->setProductType($item->getProductType())
            ->setQtyBackordered($item->getBackorders())
            ->setProduct($item->getProduct())
            ->setBaseOriginalPrice($item->getBaseOriginalPrice());

        $options = $item->getProductOrderOptions();
        if (!$options) {
            $options = $item->getProduct()->getTypeInstance()->getOrderOptions($item->getProduct());
        }
        $orderItem->setProductOptions($options);
        $this->_objectCopyService->copyFieldsetToTarget(
            'sales_convert_quote_item',
            'to_order_item',
            $item,
            $orderItem
        );

        if ($item->getParentItem()) {
            $orderItem->setQtyOrdered($orderItem->getQtyOrdered() * $item->getParentItem()->getQty());
        }

        if (!$item->getNoDiscount()) {
            $this->_objectCopyService->copyFieldsetToTarget(
                'sales_convert_quote_item',
                'to_order_item_discount',
                $item,
                $orderItem
            );
        }
        return $orderItem;
    }
}
