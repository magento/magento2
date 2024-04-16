<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained from
 * Adobe.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

class GetDiscounts
{
    /**
     * Get Discount Values
     *
     * @param Quote $quote
     * @param array $discounts
     * @return array|null
     * @throws LocalizedException
     */
    public function execute(Quote $quote, array $discounts): ?array
    {
        if (empty($discounts)) {
            return null;
        }

        $discountValues = [];
        foreach ($discounts as $value) {
            $discountData = $value->getDiscountData();
            $discountValues[] = [
                'label' => $value->getRuleLabel() ?: __('Discount'),
                'applied_to' => $discountData->getAppliedTo(),
                'amount' => [
                    'value' => $discountData->getAmount(),
                    'currency' => $quote->getQuoteCurrencyCode()
                ],
                'discount_model' => $value,
                'quote_model' => $quote
            ];
        }

        return $discountValues;
    }
}
