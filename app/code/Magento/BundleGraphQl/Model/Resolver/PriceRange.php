<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Model\Resolver;

use Magento\CatalogGraphQl\Model\PriceRangeDataProvider;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\Discount;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\ProviderPool as PriceProviderPool;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Deferred\Product as ProductDataProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Format product's pricing information for price_range field
 */
class PriceRange implements ResolverInterface
{
    /**
     * @var Discount
     */
    private Discount $discount;

    /**
     * @var PriceProviderPool
     */
    private PriceProviderPool $priceProviderPool;

    /**
     * @var ProductDataProvider
     */
    private ProductDataProvider $productDataProvider;

    /**
     * @var PriceRangeDataProvider
     */
    private PriceRangeDataProvider $priceRangeDataProvider;

    /**
     * @param PriceProviderPool $priceProviderPool
     * @param Discount $discount
     * @param ProductDataProvider|null $productDataProvider
     * @param PriceRangeDataProvider|null $priceRangeDataProvider
     */
    public function __construct(
        PriceProviderPool $priceProviderPool,
        Discount $discount,
        ProductDataProvider $productDataProvider = null,
        PriceRangeDataProvider $priceRangeDataProvider = null
    ) {
        $this->priceProviderPool = $priceProviderPool;
        $this->discount = $discount;
        $this->productDataProvider = $productDataProvider
            ?? ObjectManager::getInstance()->get(ProductDataProvider::class);
        $this->priceRangeDataProvider = $priceRangeDataProvider
            ?? ObjectManager::getInstance()->get(PriceRangeDataProvider::class);
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->productDataProvider->addProductSku($value['sku']);
        $productData = $this->productDataProvider->getProductBySku($value['sku'], $context);
        $value['model'] = $productData['model'];

        return $this->priceRangeDataProvider->prepare($context, $info, $value);
    }
}
