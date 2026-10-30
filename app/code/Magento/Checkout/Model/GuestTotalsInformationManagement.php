<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Checkout\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\GuestCart\GetGuestCart;

class GuestTotalsInformationManagement implements \Magento\Checkout\Api\GuestTotalsInformationManagementInterface
{
    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var \Magento\Checkout\Api\TotalsInformationManagementInterface
     */
    protected $totalsInformationManagement;

    /**
     * @var GetGuestCart|null
     */
    private $getGuestCart;

    /**
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Magento\Checkout\Api\TotalsInformationManagementInterface $totalsInformationManagement
     * @param GetGuestCart|null $getGuestCart
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Checkout\Api\TotalsInformationManagementInterface $totalsInformationManagement,
        ?GetGuestCart $getGuestCart = null
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->totalsInformationManagement = $totalsInformationManagement;
        $this->getGuestCart = $getGuestCart ?? ObjectManager::getInstance()->get(GetGuestCart::class);
    }

    /**
     * @inheritDoc
     */
    public function calculate(
        $cartId,
        \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
    ) {
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $this->getGuestCart->execute($cartId, (int) $quoteIdMask->getQuoteId());
        return $this->totalsInformationManagement->calculate(
            $quoteIdMask->getQuoteId(),
            $addressInformation
        );
    }
}
