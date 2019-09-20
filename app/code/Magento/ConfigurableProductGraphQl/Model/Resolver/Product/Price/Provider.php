<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Resolver\Product\Price;

use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceInfo\Factory as PriceInfoFactory;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\ProviderInterface;

/**
 * Provides product prices for configurable products
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
     * @inheritdoc
     */
    public function getMinimalFinalPrice(SaleableInterface $product): AmountInterface
    {
        $priceInfo = $this->getProductPriceInfo($product);
        return $priceInfo->getPrice(FinalPrice::PRICE_CODE)->getMinimalPrice();
    }

    /**
     * @inheritdoc
     */
    public function getMinimalRegularPrice(SaleableInterface $product): AmountInterface
    {
        $priceInfo = $this->getProductPriceInfo($product);
        return $priceInfo->getPrice(RegularPrice::PRICE_CODE)->getMinRegularAmount();
    }

    /**
     * @inheritdoc
     */
    public function getMaximalFinalPrice(SaleableInterface $product): AmountInterface
    {
        $priceInfo = $this->getProductPriceInfo($product);
        return $priceInfo->getPrice(FinalPrice::PRICE_CODE)->getMaximalPrice();
    }

    /**
     * @inheritdoc
     */
    public function getMaximalRegularPrice(SaleableInterface $product): AmountInterface
    {
        $priceInfo = $this->getProductPriceInfo($product);
        return $priceInfo->getPrice(RegularPrice::PRICE_CODE)->getMaxRegularAmount();
    }

    /**
     * @inheritdoc
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
