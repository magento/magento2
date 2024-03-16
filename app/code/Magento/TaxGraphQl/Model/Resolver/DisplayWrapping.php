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

namespace Magento\TaxGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * @inheritdoc
 */
class DisplayWrapping implements ResolverInterface
{
    /**
     * @param EnumLookup $enumLookup
     */
    public function __construct(private readonly EnumLookup $enumLookup)
    {
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (isset($value['shopping_cart_display_tax_gift_wrapping'])) {
            return $this->enumLookup->getEnumValueFromField(
                'TaxWrappingEnum',
                $value['shopping_cart_display_tax_gift_wrapping']
            );
        }
        return null;
    }
}
