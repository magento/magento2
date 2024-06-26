<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Model\Resolver\Options;

use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Deferred\Product as ProductDataProvider;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Deferred\ProductFactory as ProductDataProviderFactory;
use Magento\Framework\App\ObjectManager;
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
     * @var ProductDataProviderFactory
     */
    private ProductDataProviderFactory $productFactory;

    /**
     * @param ValueFactory $valueFactory
     * @param ProductDataProvider $product Deprecated.  Use $productFactory
     * @param ProductDataProviderFactory|null $productFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ValueFactory $valueFactory,
        ProductDataProvider $product,
        ProductDataProviderFactory $productFactory = null
    ) {
        $this->valueFactory = $valueFactory;
        $this->productFactory = $productFactory
            ?: ObjectManager::getInstance()->get(ProductDataProviderFactory::class);
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
        if (!isset($value['sku'])) {
            throw new LocalizedException(__('"sku" value should be specified'));
        }
        $product = $this->productFactory->create();
        $product->addProductSku($value['sku']);
        $product->addEavAttributes(['name']);
        $result = function () use ($value, $context, $product) {
            $productData = $product->getProductBySku($value['sku'], $context);
            /** @var \Magento\Catalog\Model\Product $productModel */
            $productModel = isset($productData['model']) ? $productData['model'] : null;
            return $productModel ? $productModel->getName() : null;
        };
        return $this->valueFactory->create($result);
    }
}
