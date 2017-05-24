<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

use Magento\Quote\Model\Quote as QuoteEntity;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\App\ObjectManager;

/**
 * @api
 */
class QuoteValidator
{
    /**
     * Maximum available number
     */
    const MAXIMUM_AVAILABLE_NUMBER = 99999999;

    /**
     * @var AllowedCountries
     */
    private $allowedCountryReader;

    /**
     * QuoteValidator constructor.
     *
     * @param AllowedCountries|null $allowedCountryReader
     */
    public function __construct(AllowedCountries $allowedCountryReader = null)
    {
        $this->allowedCountryReader = $allowedCountryReader ?: ObjectManager::getInstance()
            ->get(AllowedCountries::class);
    }

    /**
     * Validate quote amount
     *
     * @param QuoteEntity $quote
     * @param float $amount
     * @return $this
     */
    public function validateQuoteAmount(QuoteEntity $quote, $amount)
    {
        if (!$quote->getHasError() && $amount >= self::MAXIMUM_AVAILABLE_NUMBER) {
            $quote->setHasError(true);
            $quote->addMessage(__('This item price or quantity is not valid for checkout.'));
        }
        return $this;
    }

    /**
     * Validate quote before submit
     *
     * @param Quote $quote
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateBeforeSubmit(QuoteEntity $quote)
    {
        if (!$quote->isVirtual()) {
            if ($quote->getShippingAddress()->validate() !== true) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        'Please check the shipping address information. %1',
                        implode(' ', $quote->getShippingAddress()->validate())
                    )
                );
            }

            // Checks if country id present in the allowed countries list.
            if (!in_array(
                $quote->getShippingAddress()->getCountryId(),
                $this->allowedCountryReader->getAllowedCountries()
            )) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Some addresses cannot be used due to country-specific configurations.')
                );
            }

            $method = $quote->getShippingAddress()->getShippingMethod();
            $rate = $quote->getShippingAddress()->getShippingRateByCode($method);
            if (!$method || !$rate) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Please specify a shipping method.'));
            }
        }
        if ($quote->getBillingAddress()->validate() !== true) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Please check the billing address information. %1',
                    implode(' ', $quote->getBillingAddress()->validate())
                )
            );
        }
        if (!$quote->getPayment()->getMethod()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please select a valid payment method.'));
        }

        return $this;
    }
}
