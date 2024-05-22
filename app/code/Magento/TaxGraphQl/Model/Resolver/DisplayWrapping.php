<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
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
