<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

use Magento\Quote\Model\Quote as QuoteEntity;

class QuoteValidator
{
    /**
     * Maximum available number
     */
    const MAXIMUM_AVAILABLE_NUMBER = 99999999;

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
            $method = $quote->getShippingAddress()->getShippingMethod();
            $rate = $quote->getShippingAddress()->getShippingRateByCode($method);
            if (!$quote->isVirtual() && (!$method || !$rate)) {
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
