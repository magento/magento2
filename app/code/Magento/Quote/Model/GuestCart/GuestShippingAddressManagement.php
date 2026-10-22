<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Quote\Model\GuestCart;

use Magento\Quote\Model\GuestCart\GuestShippingAddressManagementInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ShippingAddressManagementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\GuestCart\GetGuestCart;

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
     * @var GetGuestCart|null
     */
    private $getGuestCart;

    /**
     * Constructs a quote shipping address write service object.
     *
     * @param ShippingAddressManagementInterface $shippingAddressManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param GetGuestCart|null $getGuestCart
     */
    public function __construct(
        ShippingAddressManagementInterface $shippingAddressManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        ?GetGuestCart $getGuestCart = null
    ) {
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->getGuestCart = $getGuestCart ?? ObjectManager::getInstance()->get(GetGuestCart::class);
    }

    /**
     * @inheritDoc
     */
    public function assign($cartId, \Magento\Quote\Api\Data\AddressInterface $address)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $this->getGuestCart->execute($cartId, (int) $quoteIdMask->getQuoteId());
        return $this->shippingAddressManagement->assign($quoteIdMask->getQuoteId(), $address);
    }

    /**
     * @inheritDoc
     */
    public function get($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $this->getGuestCart->execute($cartId, (int) $quoteIdMask->getQuoteId());
        return $this->shippingAddressManagement->get($quoteIdMask->getQuoteId());
    }
}
