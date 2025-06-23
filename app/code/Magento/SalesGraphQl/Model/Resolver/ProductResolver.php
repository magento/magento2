<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\Model\ProductDataProvider;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

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
        if (!isset($value['associatedProduct'])) {
            throw new GraphQlNoSuchEntityException(
                __("This product is currently out of stock or not available.")
            );
        }
        /** @var Product $product */
        $product = $value['associatedProduct'];

        return $this->productDataProvider->getProductDataById((int)$product->getId());
    }
}
