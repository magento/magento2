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
        $totalDiscounts = $address->getExtensionAttributes()->getDiscounts();
        if ($totalDiscounts && is_array($totalDiscounts)) {
            foreach ($totalDiscounts as $value) {
                $discount = [];
                $amount = [];
                $discount['label'] = $value->getRuleLabel() ?: __('Discount');
                /* @var \Magento\SalesRule\Api\Data\DiscountDataInterface $discountData */
                $discountData = $value->getDiscountData();
                $amount['value'] = $discountData->getAmount();
                $amount['currency'] = $quote->getQuoteCurrencyCode();
                $discount['amount'] = $amount;
                $discountValues[] = $discount;
            }
            return $discountValues;
        }
        return null;
    }
}
