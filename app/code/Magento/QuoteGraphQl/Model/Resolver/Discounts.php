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
        return $this->getDiscountValues($value['discount']);
    }

    /**
     * Get Discount Values
     *
     * @param array $discountInfo
     * @return array|null
     */
    private function getDiscountValues(array $discountInfo = [])
    {
        if (!empty($discountInfo)) {
            $discountInfo['label'] = __(current($discountInfo['label']) ?: 'Discount');
            return [$discountInfo];
        } else {
            return null;
        }
    }
}
