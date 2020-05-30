<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\Price;

use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\SaleableInterface;

/**
 * Provides product prices
 */
class Provider implements ProviderInterface
{
    /**
     * @inheritdoc
     */
    public function getMinimalFinalPrice(SaleableInterface $product): AmountInterface
    {
        /** @var FinalPrice $finalPrice */
        $finalPrice =  $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE);
        return $finalPrice->getMinimalPrice();
    }

    /**
     * @inheritdoc
     */
    public function getMinimalRegularPrice(SaleableInterface $product): AmountInterface
    {
        return $this->getRegularPrice($product);
    }

    /**
     * @inheritdoc
     */
    public function getMaximalFinalPrice(SaleableInterface $product): AmountInterface
    {
        /** @var FinalPrice $finalPrice */
        $finalPrice = $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE);
        return $finalPrice->getMaximalPrice();
    }

    /**
     * @inheritdoc
     */
    public function getMaximalRegularPrice(SaleableInterface $product): AmountInterface
    {
        return $this->getRegularPrice($product);
    }

    /**
     * @inheritdoc
     */
    public function getRegularPrice(SaleableInterface $product): AmountInterface
    {
        return $product->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getAmount();
    }
}
