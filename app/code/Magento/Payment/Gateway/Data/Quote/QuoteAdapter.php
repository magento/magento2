<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Data\Quote;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;

/**
 * Class QuoteAdapter
 * @since 2.0.0
 */
class QuoteAdapter implements OrderAdapterInterface
{
    /**
     * @var CartInterface
     * @since 2.0.0
     */
    private $quote;

    /**
     * @var AddressAdapter
     * @since 2.0.0
     */
    private $addressAdapterFactory;

    /**
     * @param CartInterface $quote
     * @param AddressAdapterFactory $addressAdapterFactory
     * @since 2.0.0
     */
    public function __construct(
        CartInterface $quote,
        AddressAdapterFactory $addressAdapterFactory
    ) {
        $this->quote = $quote;
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
        return $this->quote->getCurrency()->getBaseCurrencyCode();
    }

    /**
     * Returns order increment id
     *
     * @return string
     * @since 2.0.0
     */
    public function getOrderIncrementId()
    {
        return $this->quote->getReservedOrderId();
    }

    /**
     * Returns customer ID
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getCustomerId()
    {
        return $this->quote->getCustomer()->getId();
    }

    /**
     * Returns billing address
     *
     * @return AddressAdapterInterface|null
     * @since 2.0.0
     */
    public function getBillingAddress()
    {
        if ($this->quote->getBillingAddress()) {
            return $this->addressAdapterFactory->create(
                ['address' => $this->quote->getBillingAddress()]
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
        if ($this->quote->getShippingAddress()) {
            return $this->addressAdapterFactory->create(
                ['address' => $this->quote->getShippingAddress()]
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
        return $this->quote->getStoreId();
    }

    /**
     * Returns order id
     *
     * @return int
     * @since 2.0.0
     */
    public function getId()
    {
        return $this->quote->getId();
    }

    /**
     * Returns order grand total amount
     *
     * @return null
     * @since 2.0.0
     */
    public function getGrandTotalAmount()
    {
        return null;
    }

    /**
     * Returns list of line items in the cart
     *
     * @return \Magento\Quote\Api\Data\CartItemInterface[]|null
     * @since 2.0.0
     */
    public function getItems()
    {
        return $this->quote->getItems();
    }

    /**
     * Gets the remote IP address for the order.
     *
     * @return string|null Remote IP address.
     * @since 2.0.0
     */
    public function getRemoteIp()
    {
        return null;
    }
}
