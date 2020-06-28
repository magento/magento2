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
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Products by ID resolver, used for GraphQL request processing.
 */
class ProductsByID implements ResolverInterface
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
     * ProductsByID constructor.
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
        $productIds = ($args['ids'] ?? []);
        $fields = $this->productFieldsSelector->getProductFieldsFromInfo($info);

        /** @var \Magento\Catalog\Model\Product[] $products */
        $products = $this->productDataProvider->getProductByIds($productIds, $fields);
        $data = [];

        foreach ($products as $product) {
            $productData = $product->getData();
            $productData['model'] = $product;
            $data[] = $productData;
        }

        return $data;
    }
}
