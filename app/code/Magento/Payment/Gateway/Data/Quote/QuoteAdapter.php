<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Data\Quote;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;

/**
 * Class QuoteAdapter
 */
class QuoteAdapter implements OrderAdapterInterface
{
    /**
     * @var CartInterface
     */
    private $quote;

    /**
     * @var AddressAdapter
     */
    private $addressAdapterFactory;

    /**
     * @param CartInterface $quote
     * @param AddressAdapterFactory $addressAdapterFactory
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
     */
    public function getCurrencyCode()
    {
        return $this->quote->getCurrency()->getBaseCurrencyCode();
    }

    /**
     * Returns order increment id
     *
     * @return string
     */
    public function getOrderIncrementId()
    {
        return $this->quote->getReservedOrderId();
    }

    /**
     * Returns customer ID
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->quote->getCustomer()->getId();
    }

    /**
     * Returns billing address
     *
     * @return AddressAdapterInterface|null
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
     */
    public function getStoreId()
    {
        return $this->quote->getStoreId();
    }

    /**
     * Returns order id
     *
     * @return int
     */
    public function getId()
    {
        return $this->quote->getId();
    }

    /**
     * Returns order grand total amount
     *
     * @return null
     */
    public function getGrandTotalAmount()
    {
        return null;
    }

    /**
     * Returns list of line items in the cart
     *
     * @return \Magento\Quote\Api\Data\CartItemInterface[]|null
     */
    public function getItems()
    {
        return $this->quote->getItems();
    }

    /**
     * Gets the remote IP address for the order.
     *
     * @return string|null Remote IP address.
     */
    public function getRemoteIp()
    {
        return null;
    }
}
