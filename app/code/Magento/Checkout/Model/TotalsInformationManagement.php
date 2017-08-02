<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model;

/**
 * Class TotalsInformationManagement
 * @since 2.0.0
 */
class TotalsInformationManagement implements \Magento\Checkout\Api\TotalsInformationManagementInterface
{
    /**
     * Cart total repository.
     *
     * @var \Magento\Quote\Api\CartTotalRepositoryInterface
     * @since 2.0.0
     */
    protected $cartTotalRepository;

    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     * @since 2.0.0
     */
    protected $cartRepository;

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalRepository
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalRepository
    ) {
        $this->cartRepository = $cartRepository;
        $this->cartTotalRepository = $cartTotalRepository;
    }

    /**
     * {@inheritDoc}
     * @since 2.0.0
     */
    public function calculate(
        $cartId,
        \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
    ) {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->cartRepository->get($cartId);
        $this->validateQuote($quote);

        if ($quote->getIsVirtual()) {
            $quote->setBillingAddress($addressInformation->getAddress());
        } else {
            $quote->setShippingAddress($addressInformation->getAddress());
            $quote->getShippingAddress()->setCollectShippingRates(true)->setShippingMethod(
                $addressInformation->getShippingCarrierCode() . '_' . $addressInformation->getShippingMethodCode()
            );
        }
        $quote->collectTotals();

        return $this->cartTotalRepository->get($cartId);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     * @since 2.0.0
     */
    protected function validateQuote(\Magento\Quote\Model\Quote $quote)
    {
        if ($quote->getItemsCount() === 0) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Totals calculation is not applicable to empty cart')
            );
        }
    }
}
