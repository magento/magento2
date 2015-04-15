<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Quote\Model\GuestCart;

use Magento\Quote\Api\GuestShippingMethodManagementInterface;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\ShippingMethodManagement;

/**
 * Shipping method management class for guest carts.
 */
class GuestShippingMethodManagement extends ShippingMethodManagement implements GuestShippingMethodManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * Constructs a shipping method read service object.
     *
     * @param QuoteRepository $quoteRepository Quote repository.
     * @param \Magento\Quote\Api\Data\ShippingMethodInterfaceFactory $methodDataFactory Shipping method factory.
     * @param ShippingMethodConverter $converter Shipping method converter.
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        \Magento\Quote\Api\Data\ShippingMethodInterfaceFactory $methodDataFactory,
        ShippingMethodConverter $converter,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->methodDataFactory = $methodDataFactory;
        $this->converter = $converter;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function get($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return parent::get($quoteIdMask->getId());
    }

    /**
     * {@inheritDoc}
     */
    public function getList($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return parent::getList($quoteIdMask->getId());
    }

    /**
     * {@inheritDoc}
     */
    public function set($cartId, $carrierCode, $methodCode)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return parent::set($quoteIdMask->getId(), $carrierCode, $methodCode);
    }
}
