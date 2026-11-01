<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Model\Resolver\Options;

use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Deferred\Product as ProductDataProvider;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Deferred\ProductFactory as ProductDataProviderFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Bundle product option label resolver
 */
class Label implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private ValueFactory $valueFactory;

    /**
     * @var ProductDataProvider
     */
    private ProductDataProvider $productDataProvider;

    /**
     * @param ValueFactory $valueFactory
     * @param ProductDataProvider $product
     * @param ProductDataProviderFactory|null $productFactory @deprecated
     * @param ProductDataProvider|null $productDataProvider
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ValueFactory $valueFactory,
        ProductDataProvider $product,
        ?ProductDataProviderFactory $productFactory = null,
        ?ProductDataProvider $productDataProvider = null
    ) {
        $this->valueFactory = $valueFactory;
        $this->productDataProvider = $productDataProvider ?? $product;
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
        if (!isset($value['sku'])) {
            throw new LocalizedException(__('"sku" value should be specified'));
        }
        $this->productDataProvider->addProductSku($value['sku']);
        $this->productDataProvider->addEavAttributes(['name']);
        $result = function () use ($value, $context) {
            $productData = $this->productDataProvider->getProductBySku($value['sku'], $context);
            /** @var \Magento\Catalog\Model\Product $productModel */
            $productModel = isset($productData['model']) ? $productData['model'] : null;
            return $productModel ? $productModel->getName() : null;
        };
        return $this->valueFactory->create($result);
    }
}
