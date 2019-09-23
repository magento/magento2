<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Model\Resolver\Product\Price;

use Magento\Bundle\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\ProviderInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\SaleableInterface;

/**
 * Provides pricing information for Bundle products
 */
class Provider implements ProviderInterface
{
    /**
     * @inheritdoc
     */
    public function getMinimalFinalPrice(SaleableInterface $product): AmountInterface
    {
        return $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getMinimalPrice();
    }

    /**
     * @inheritdoc
     */
    public function getMinimalRegularPrice(SaleableInterface $product): AmountInterface
    {
        return $product->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getMinimalPrice();
    }

    /**
     * @inheritdoc
     */
    public function getMaximalFinalPrice(SaleableInterface $product): AmountInterface
    {
        return $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getMaximalPrice();
    }

    /**
     * @inheritdoc
     */
    public function getMaximalRegularPrice(SaleableInterface $product): AmountInterface
    {
        return $product->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getMaximalPrice();
    }

    /**
     * @inheritdoc
     */
    public function getRegularPrice(SaleableInterface $product): AmountInterface
    {
        return $product->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getAmount();
    }
}
