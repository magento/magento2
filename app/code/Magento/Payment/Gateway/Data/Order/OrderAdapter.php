<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Data\Order;

use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Sales\Model\Order;

/**
 * Class OrderAdapter
 * @since 2.0.0
 */
class OrderAdapter implements OrderAdapterInterface
{
    /**
     * @var Order
     * @since 2.0.0
     */
    private $order;

    /**
     * @var AddressAdapter
     * @since 2.0.0
     */
    private $addressAdapterFactory;

    /**
     * @param Order $order
     * @param AddressAdapterFactory $addressAdapterFactory
     * @since 2.0.0
     */
    public function __construct(
        Order $order,
        AddressAdapterFactory $addressAdapterFactory
    ) {
        $this->order = $order;
        $this->addressAdapterFactory = $addressAdapterFactory;
    }

    /**
     * Returns currency code
     *
     * @return string
     * @since 2.0.0
     */
    public function getCurrencyCode()
    {
        return $this->order->getBaseCurrencyCode();
    }

    /**
     * Returns order increment id
     *
     * @return string
     * @since 2.0.0
     */
    public function getOrderIncrementId()
    {
        return $this->order->getIncrementId();
    }

    /**
     * Returns customer ID
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getCustomerId()
    {
        return $this->order->getCustomerId();
    }

    /**
     * Returns billing address
     *
     * @return AddressAdapterInterface|null
     * @since 2.0.0
     */
    public function getBillingAddress()
    {
        if ($this->order->getBillingAddress()) {
            return $this->addressAdapterFactory->create(
                ['address' => $this->order->getBillingAddress()]
            );
        }

        return null;
    }

    /**
     * Returns shipping address
     *
     * @return AddressAdapterInterface|null
     * @since 2.0.0
     */
    public function getShippingAddress()
    {
        if ($this->order->getShippingAddress()) {
            return $this->addressAdapterFactory->create(
                ['address' => $this->order->getShippingAddress()]
            );
        }

        return null;
    }

    /**
     * Returns order store id
     *
     * @return int
     * @since 2.0.0
     */
    public function getStoreId()
    {
        return $this->order->getStoreId();
    }

    /**
     * Returns order id
     *
     * @return int
     * @since 2.0.0
     */
    public function getId()
    {
        return $this->order->getEntityId();
    }

    /**
     * Returns order grand total amount
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getGrandTotalAmount()
    {
        return $this->order->getBaseGrandTotal();
    }

    /**
     * Returns list of line items in the cart
     *
     * @return \Magento\Sales\Api\Data\OrderItemInterface[]
     * @since 2.0.0
     */
    public function getItems()
    {
        return $this->order->getItems();
    }

    /**
     * Gets the remote IP address for the order.
     *
     * @return string|null Remote IP address.
     * @since 2.0.0
     */
    public function getRemoteIp()
    {
        return $this->order->getRemoteIp();
    }
}
