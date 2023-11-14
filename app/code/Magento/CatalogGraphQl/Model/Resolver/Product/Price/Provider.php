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
     * @var array
     */
    private $minimalPrice = [
        FinalPrice::PRICE_CODE => [],
        RegularPrice::PRICE_CODE => []
    ];

    /**
     * @var array
     */
    private $maximalPrice = [
        FinalPrice::PRICE_CODE => [],
        RegularPrice::PRICE_CODE => []
    ];

    /**
     * @inheritdoc
     */
    public function getMinimalFinalPrice(SaleableInterface $product): AmountInterface
    {
        if (!isset($this->minimalPrice[FinalPrice::PRICE_CODE][$product->getId()])) {
            /** @var FinalPrice $finalPrice */
            $finalPrice = $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE);
            $this->minimalPrice[FinalPrice::PRICE_CODE][$product->getId()] = $finalPrice->getMinimalPrice();
        }
        return $this->minimalPrice[FinalPrice::PRICE_CODE][$product->getId()];
    }

    /**
     * @inheritdoc
     */
    public function getMinimalRegularPrice(SaleableInterface $product): AmountInterface
    {
        if (!isset($this->minimalPrice[RegularPrice::PRICE_CODE][$product->getId()])) {
            $this->minimalPrice[RegularPrice::PRICE_CODE][$product->getId()] = $this->getRegularPrice($product);
        }
        return $this->minimalPrice[RegularPrice::PRICE_CODE][$product->getId()];
    }

    /**
     * @inheritdoc
     */
    public function getMaximalFinalPrice(SaleableInterface $product): AmountInterface
    {
        if (!isset($this->maximalPrice[FinalPrice::PRICE_CODE][$product->getId()])) {
            /** @var FinalPrice $finalPrice */
            $finalPrice =  $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE);
            $this->maximalPrice[FinalPrice::PRICE_CODE][$product->getId()] = $finalPrice->getMaximalPrice();
        }
        return $this->maximalPrice[FinalPrice::PRICE_CODE][$product->getId()];
    }

    /**
     * @inheritdoc
     */
    public function getMaximalRegularPrice(SaleableInterface $product): AmountInterface
    {
        if (!isset($this->maximalPrice[RegularPrice::PRICE_CODE][$product->getId()])) {
            $this->maximalPrice[RegularPrice::PRICE_CODE][$product->getId()] = $this->getRegularPrice($product);
        }
        return $this->maximalPrice[RegularPrice::PRICE_CODE][$product->getId()];
    }

    /**
     * @inheritdoc
     */
    public function getRegularPrice(SaleableInterface $product): AmountInterface
    {
        return $product->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getAmount();
    }
}
