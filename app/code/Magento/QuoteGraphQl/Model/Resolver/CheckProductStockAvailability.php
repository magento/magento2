<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\CartItem\ProductStock;
use Magento\Quote\Model\Quote\Item;

/**
 * @inheritdoc
 */
class CheckProductStockAvailability implements ResolverInterface
{
    /**
     * CheckProductStockAvailability constructor
     *
     * @param ProductStock $productStock
     */
    public function __construct(
        private ProductStock $productStock
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Item $cartItem */
        $cartItem = $value['model'];

        return $this->productStock->isProductAvailable($cartItem);
    }
}
