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
use Magento\QuoteGraphQl\Model\Cart\DiscountAggregator;

/**
 * @inheritdoc
 */
class Discounts implements ResolverInterface
{
    /**
     * @var DiscountAggregator
     */
    private $discountAggregator;

    /**
     * @param DiscountAggregator|null $discountAggregator
     */
    public function __construct(
        DiscountAggregator $discountAggregator
    ) {
        $this->discountAggregator = $discountAggregator;
    }

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
        $totalDiscounts = $this->discountAggregator->aggregateDiscountPerRule($quote);
        if ($totalDiscounts) {
            foreach ($totalDiscounts as $value) {
                $discount = [];
                $amount = [];
                /* @var \Magento\SalesRule\Model\Rule $rule*/
                $rule = $value['rule'];
                $discount['label'] = $rule->getStoreLabel($quote->getStore()) ?: __('Discount');
                $amount['value'] = $value['discount'];
                $amount['currency'] = $quote->getQuoteCurrencyCode();
                $discount['amount'] = $amount;
                $discountValues[] = $discount;
            }
            return $discountValues;
        }
        return null;
    }
}
