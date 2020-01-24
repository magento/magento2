<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Resolver\Product\Price;

use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableRegularPrice;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\ProviderInterface;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface;

/**
 * Provides product prices for configurable products
 */
class Provider implements ProviderInterface
{
    /**
     * @var ConfigurableOptionsProviderInterface
     */
    private $optionsProvider;

    /**
     * @var array
     */
    private $minimumFinalAmounts = [];

    /**
     * @var array
     */
    private $maximumFinalAmounts = [];

    /**
     * @param ConfigurableOptionsProviderInterface $optionsProvider
     */
    public function __construct(
        ConfigurableOptionsProviderInterface $optionsProvider
    ) {
        $this->optionsProvider = $optionsProvider;
    }

    /**
     * @inheritdoc
     */
    public function getMinimalFinalPrice(SaleableInterface $product): AmountInterface
    {
        if (!isset($this->minimumFinalAmounts[$product->getId()])) {
            $minimumAmount = null;
            foreach ($this->optionsProvider->getProducts($product) as $variant) {
                $variantAmount = $variant->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getAmount();
                if (!$minimumAmount || ($variantAmount->getValue() < $minimumAmount->getValue())) {
                    $minimumAmount = $variantAmount;
                    $this->minimumFinalAmounts[$product->getId()] = $variantAmount;
                }
            }
        }

        return $this->minimumFinalAmounts[$product->getId()];
    }

    /**
     * @inheritdoc
     */
    public function getMinimalRegularPrice(SaleableInterface $product): AmountInterface
    {
        /** @var ConfigurableRegularPrice $regularPrice */
        $regularPrice = $product->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE);
        return $regularPrice->getMinRegularAmount();
    }

    /**
     * @inheritdoc
     */
    public function getMaximalFinalPrice(SaleableInterface $product): AmountInterface
    {
        if (!isset($this->maximumFinalAmounts[$product->getId()])) {
            $maximumAmount = null;
            foreach ($this->optionsProvider->getProducts($product) as $variant) {
                $variantAmount = $variant->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getAmount();
                if (!$maximumAmount || ($variantAmount->getValue() > $maximumAmount->getValue())) {
                    $maximumAmount = $variantAmount;
                    $this->maximumFinalAmounts[$product->getId()] = $variantAmount;
                }
            }
        }

        return $this->maximumFinalAmounts[$product->getId()];
    }

    /**
     * @inheritdoc
     */
    public function getMaximalRegularPrice(SaleableInterface $product): AmountInterface
    {
        /** @var ConfigurableRegularPrice $regularPrice */
        $regularPrice = $product->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE);
        return $regularPrice->getMaxRegularAmount();
    }

    /**
     * @inheritdoc
     */
    public function getRegularPrice(SaleableInterface $product): AmountInterface
    {
        return $product->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getAmount();
    }
}
