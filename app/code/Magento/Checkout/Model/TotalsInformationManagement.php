<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Checkout\Model;

use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Checkout\Api\TotalsInformationManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * Class TotalsInformationManagement
 */
class TotalsInformationManagement implements TotalsInformationManagementInterface
{
    /**
     * Cart total repository.
     *
     * @var CartTotalRepositoryInterface
     */
    protected $cartTotalRepository;

    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param CartTotalRepositoryInterface $cartTotalRepository
     * @codeCoverageIgnore
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        CartTotalRepositoryInterface $cartTotalRepository
    ) {
        $this->cartRepository = $cartRepository;
        $this->cartTotalRepository = $cartTotalRepository;
    }

    /**
     * Calculate quote totals based on address and shipping method. Save the quote.
     * On multi-shipping we are skipping address assignment because we already have address which is enough for
     * totals calculation
     *
     * @inheritDoc
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function calculate($cartId, TotalsInformationInterface $addressInformation)
    {
        /** @var Quote $quote */
        $quote = $this->cartRepository->get($cartId);
        $this->validateQuote($quote);

        if ($quote->getIsVirtual()) {
            $quote->setBillingAddress($addressInformation->getAddress());
        } elseif (!$quote->isMultipleShippingAddresses()) {
            $quote->setShippingAddress($addressInformation->getAddress());
            $quote->getShippingAddress()->setCollectShippingRates(true)->setShippingMethod(
                $addressInformation->getShippingCarrierCode() . '_' . $addressInformation->getShippingMethodCode()
            );
        }
        $this->cartRepository->save($quote);

        return $this->cartTotalRepository->get($cartId);
    }

    /**
     * Check number of quote items. Totals calculation is not applicable to an empty cart
     *
     * @param Quote $quote
     * @throws LocalizedException
     * @return void
     */
    protected function validateQuote(Quote $quote)
    {
        if ($quote->getItemsCount() === 0) {
            throw new LocalizedException(
                __('Totals calculation is not applicable to an empty cart')
            );
        }
    }
}
