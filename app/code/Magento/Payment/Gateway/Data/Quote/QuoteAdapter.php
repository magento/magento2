<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @return AddressAdapterInterface
     */
    public function getBillingAddress()
    {
        return $this->addressAdapterFactory->create(
            ['address' => $this->quote->getBillingAddress()]
        );
    }

    /**
     * Returns shipping address
     *
     * @return AddressAdapterInterface
     */
    public function getShippingAddress()
    {
        return $this->addressAdapterFactory->create(
            ['address' => $this->quote->getShippingAddress()]
        );
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
}
