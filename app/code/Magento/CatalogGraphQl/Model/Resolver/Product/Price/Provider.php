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
use Magento\Framework\Pricing\PriceInfo\Factory as PriceInfoFactory;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\Pricing\SaleableInterface;

/**
 * Provides product prices
 */
class Provider implements ProviderInterface
{
    /**
     * PriceInfo cache
     *
     * @var array
     */
    private $productPrices = [];

    /**
     * @var PriceInfoFactory
     */
    private $priceInfoFactory;

    /**
     * @param PriceInfoFactory $priceInfoFactory
     */
    public function __construct(
        PriceInfoFactory $priceInfoFactory
    ) {
        $this->priceInfoFactory = $priceInfoFactory;
    }

    /**
     * Get the product minimal final price
     *
     * @param SaleableInterface $product
     * @return AmountInterface
     */
    public function getMinimalFinalPrice(SaleableInterface $product): AmountInterface
    {
        $priceInfo = $this->getProductPriceInfo($product);
        $finalPrice = $priceInfo->getPrice(FinalPrice::PRICE_CODE);
        return $finalPrice->getMinimalPrice();
    }

    /**
     * Get the product minimal regular price
     *
     * @param SaleableInterface $product
     * @return AmountInterface
     */
    public function getMinimalRegularPrice(SaleableInterface $product): AmountInterface
    {
        return $this->getRegularPrice($product);
    }

    /**
     * Get the product maximum final price
     *
     * @param SaleableInterface $product
     * @return AmountInterface
     */
    public function getMaximalFinalPrice(SaleableInterface $product): AmountInterface
    {
        $priceInfo = $this->getProductPriceInfo($product);
        $finalPrice = $priceInfo->getPrice(FinalPrice::PRICE_CODE);
        return $finalPrice->getMaximalPrice();
    }

    /**
     * Get the product maximum final price
     *
     * @param SaleableInterface $product
     * @return AmountInterface
     */
    public function getMaximalRegularPrice(SaleableInterface $product): AmountInterface
    {
        return $this->getRegularPrice($product);
    }

    /**
     * Get the product regular price
     *
     * @param SaleableInterface $product
     * @return AmountInterface
     */
    public function getRegularPrice(SaleableInterface $product): AmountInterface
    {
        $priceInfo = $this->getProductPriceInfo($product);
        return $priceInfo->getPrice(RegularPrice::PRICE_CODE)->getAmount();
    }

    /**
     * Get price info for product
     *
     * @param SaleableInterface $product
     * @return PriceInfoInterface
     */
    private function getProductPriceInfo($product)
    {
        if (!isset($this->productPrices[$product->getId()])) {
            $this->productPrices[$product->getId()] = $this->priceInfoFactory->create($product);
        }

        return $this->productPrices[$product->getId()];
    }
}
