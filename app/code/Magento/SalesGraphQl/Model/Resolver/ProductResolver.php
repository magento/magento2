<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\CatalogGraphQl\Model\ProductDataProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Fetches the Product data according to the GraphQL schema
 */
class ProductResolver implements ResolverInterface
{
    /**
     * ProductResolver Constructor
     *
     * @param ProductDataProvider $productDataProvider
     */
    public function __construct(
        private readonly ProductDataProvider $productDataProvider
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
    ) {
        if (!(($value['model'] ?? null) instanceof OrderItemInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        return $this->productDataProvider->getProductDataById((int)$value['model']->getProductId());
    }
}
