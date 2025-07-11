<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
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
        $discounts = $discounts ?: $quote->getBillingAddress()->getExtensionAttributes()->getDiscounts() ?? [];

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
