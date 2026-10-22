<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Quote\Model\GuestCart;

use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Api\GuestPaymentMethodManagementInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\GuestCart\GetGuestCart;
use Magento\Framework\App\ObjectManager;

/**
 * Payment method management class for guest carts.
 */
class GuestPaymentMethodManagement implements GuestPaymentMethodManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var GetGuestCart|null
     */
    private $getGuestCart;

    /**
     * Initialize dependencies.
     *
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param GetGuestCart|null $getGuestCart
     */
    public function __construct(
        PaymentMethodManagementInterface $paymentMethodManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        ?GetGuestCart $getGuestCart = null
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->getGuestCart = $getGuestCart ?? ObjectManager::getInstance()->get(GetGuestCart::class);
    }

    /**
     * @inheritDoc
     */
    public function set($cartId, \Magento\Quote\Api\Data\PaymentInterface $method)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $this->getGuestCart->execute($cartId, (int) $quoteIdMask->getQuoteId());
        return $this->paymentMethodManagement->set($quoteIdMask->getQuoteId(), $method);
    }

    /**
     * @inheritDoc
     */
    public function get($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $this->getGuestCart->execute($cartId, (int) $quoteIdMask->getQuoteId());
        return $this->paymentMethodManagement->get($quoteIdMask->getQuoteId());
    }

    /**
     * @inheritDoc
     */
    public function getList($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $this->getGuestCart->execute($cartId, (int) $quoteIdMask->getQuoteId());
        return $this->paymentMethodManagement->getList($quoteIdMask->getQuoteId());
    }
}
