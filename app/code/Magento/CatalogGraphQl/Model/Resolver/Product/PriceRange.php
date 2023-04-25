<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\CatalogGraphQl\Model\PriceRangeDataProvider;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\Discount;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\ProviderPool as PriceProviderPool;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
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
     * @var PriceRangeDataProvider
     */
    private PriceRangeDataProvider $priceRangeDataProvider;

    /**
     * @param PriceProviderPool $priceProviderPool
     * @param Discount $discount
     * @param PriceRangeDataProvider|null $priceRangeDataProvider
     */
    public function __construct(
        PriceProviderPool $priceProviderPool,
        Discount $discount,
        PriceRangeDataProvider $priceRangeDataProvider = null
    ) {
        $this->priceProviderPool = $priceProviderPool;
        $this->discount = $discount;
        $this->priceRangeDataProvider = $priceRangeDataProvider
            ?? ObjectManager::getInstance()->get(PriceRangeDataProvider::class);
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
        return $this->priceRangeDataProvider->prepare($context, $info, $value);
    }
}
