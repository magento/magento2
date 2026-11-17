<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Quote\Model\GuestCart;

use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Api\GuestCartTotalManagementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\GuestCart\GetGuestCart;
use Magento\Quote\Api\CartTotalManagementInterface;

/**
 * @inheritDoc
 */
class GuestCartTotalManagement implements GuestCartTotalManagementInterface
{
    /**
     * @var CartTotalManagementInterface
     */
    protected $cartTotalManagement;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var GetGuestCart|null
     */
    private $getGuestCart;

    /**
     * @param CartTotalManagementInterface $cartTotalManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param GetGuestCart|null $getGuestCart
     */
    public function __construct(
        CartTotalManagementInterface $cartTotalManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        ?GetGuestCart $getGuestCart = null
    ) {
        $this->cartTotalManagement = $cartTotalManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->getGuestCart = $getGuestCart ?? ObjectManager::getInstance()->get(GetGuestCart::class);
    }

    /**
     * @inheritDoc
     */
    public function collectTotals(
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        $shippingCarrierCode = null,
        $shippingMethodCode = null,
        ?\Magento\Quote\Api\Data\TotalsAdditionalDataInterface $additionalData = null
    ) {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $this->getGuestCart->execute($cartId, (int) $quoteIdMask->getQuoteId());
        return $this->cartTotalManagement->collectTotals(
            $quoteIdMask->getQuoteId(),
            $paymentMethod,
            $shippingCarrierCode,
            $shippingMethodCode,
            $additionalData
        );
    }
}
