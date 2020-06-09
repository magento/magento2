<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver;

use Magento\CatalogGraphQl\Model\Resolver\Product\DataProvider\ProductDataProviderInterface;
use Magento\CatalogGraphQl\Model\Resolver\Product\ProductFieldsSelector;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Product by ID resolver, used for GraphQL request processing.
 */
class ProductId implements ResolverInterface
{
    /**
     * @var ProductDataProviderInterface
     */
    private $productDataProvider;

    /**
     * @var ProductFieldsSelector
     */
    private $productFieldsSelector;

    /**
     * ProductId constructor.
     *
     * @param ProductDataProviderInterface $productDataProvider
     * @param ProductFieldsSelector $productFieldsSelector
     */
    public function __construct(
        ProductDataProviderInterface $productDataProvider,
        ProductFieldsSelector $productFieldsSelector
    ) {
        $this->productDataProvider = $productDataProvider;
        $this->productFieldsSelector = $productFieldsSelector;
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
        $productId = (int) ($args['id'] ?? 0);
        $fields = $this->productFieldsSelector->getProductFieldsFromInfo($info);

        if (!$productId) {
            throw new GraphQlInputException(
                __("'id' input argument is required.")
            );
        }

        if ($productId < 0) {
            throw new GraphQlInputException(
                __("'id' input argument should not be negative.")
            );
        }

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productDataProvider->getProductById($productId, $fields);
        $data = $product->getData();
        $data['model'] = $product;

        return $data;
    }
}
