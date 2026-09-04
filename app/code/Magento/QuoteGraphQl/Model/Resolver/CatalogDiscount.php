<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\Discount;
use Magento\Quote\Api\Data\CartItemInterface;

class CatalogDiscount implements ResolverInterface
{
    /**
     * CatalogDiscount constructor
     *
     * @param Discount $discount
     */
    public function __construct(
        private readonly Discount $discount
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ):array {
        if (!(($value['model'] ?? null) instanceof CartItemInterface) ||
            empty($value['original_item_price']) ||
            empty($value['price'])
        ) {
            throw new LocalizedException(__('The "model" value or pricing details are missing or invalid.'));
        }

        return $this->discount->getDiscountByDifference(
            $value['original_item_price']['value'],
            $value['price']['value']
        );
    }
}
