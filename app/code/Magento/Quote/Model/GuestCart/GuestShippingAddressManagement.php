<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\GuestCart;

use Magento\Quote\Model\GuestCart\GuestShippingAddressManagementInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ShippingAddressManagementInterface;

/**
 * Shipping address management class for guest carts.
 */
class GuestShippingAddressManagement implements GuestShippingAddressManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var ShippingAddressManagementInterface
     */
    protected $shippingAddressManagement;

    /**
     * Constructs a quote shipping address write service object.
     *
     * @param ShippingAddressManagementInterface $shippingAddressManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        ShippingAddressManagementInterface $shippingAddressManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function assign($cartId, \Magento\Quote\Api\Data\AddressInterface $address)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->shippingAddressManagement->assign($quoteIdMask->getQuoteId(), $address);
    }

    /**
     * {@inheritDoc}
     */
    public function get($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->shippingAddressManagement->get($quoteIdMask->getQuoteId());
    }
}
