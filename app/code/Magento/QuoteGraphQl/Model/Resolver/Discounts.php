<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote;

/**
 * @inheritdoc
 */
class Discounts implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        $quote = $value['model'];

        return $this->getDiscountValues($quote);
    }

    /**
     * Get Discount Values
     *
     * @param Quote $quote
     * @return array
     */
    private function getDiscountValues(Quote $quote)
    {
        $discountValues=[];
        $address = $quote->getShippingAddress();
        $totals = $address->getTotals();
        if ($totals && is_array($totals)) {
            foreach ($totals as $total) {
                if (stripos($total->getCode(), 'total') === false && $total->getValue() < 0.00) {
                    $discount = [];
                    $amount = [];
                    $discount['label'] = $total->getTitle() ?: __('Discount');
                    $amount['value'] = $total->getValue() * -1;
                    $amount['currency'] = $quote->getQuoteCurrencyCode();
                    $discount['amount'] = $amount;
                    $discountValues[] = $discount;
                }
            }
            return $discountValues;
        }
        return null;
    }
}
