<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Resolver\Product\Price;

use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\Provider as CatalogPriceProvider;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\ProviderInterface;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterfaceFactory;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Amount\BaseFactory;
use Magento\Framework\Pricing\SaleableInterface;

/**
 * Provides product prices for configurable products
 */
class Provider implements ProviderInterface, ResetAfterRequestInterface
{
    /**
     * @var ConfigurableOptionsProviderInterface
     */
    private $optionsProvider;

    /**
     * @var ConfigurableOptionsProviderInterfaceFactory
     */
    private $optionsProviderFactory;

    /**
     * @var BaseFactory
     */
    private $amountFactory;

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
     * @var CatalogPriceProvider
     */
    private $catalogPriceProvider;

    /**
     * @param ConfigurableOptionsProviderInterfaceFactory $optionsProviderFactory
     * @param BaseFactory $amountFactory
     * @param CatalogPriceProvider $catalogPriceProvider
     */
    public function __construct(
        ConfigurableOptionsProviderInterfaceFactory $optionsProviderFactory,
        BaseFactory $amountFactory,
        CatalogPriceProvider $catalogPriceProvider
    ) {
        $this->optionsProvider = $optionsProviderFactory->create();
        $this->optionsProviderFactory = $optionsProviderFactory;
        $this->amountFactory = $amountFactory;
        $this->catalogPriceProvider = $catalogPriceProvider;
    }

    /**
     * @inheritdoc
     */
    public function getMinimalFinalPrice(SaleableInterface $product): AmountInterface
    {
        return $this->getMinimalPrice($product, FinalPrice::PRICE_CODE);
    }

    /**
     * @inheritdoc
     */
    public function getMinimalRegularPrice(SaleableInterface $product): AmountInterface
    {
        return $this->getMinimalPrice($product, RegularPrice::PRICE_CODE);
    }

    /**
     * @inheritdoc
     */
    public function getMaximalFinalPrice(SaleableInterface $product): AmountInterface
    {
        return $this->getMaximalPrice($product, FinalPrice::PRICE_CODE);
    }

    /**
     * @inheritdoc
     */
    public function getMaximalRegularPrice(SaleableInterface $product): AmountInterface
    {
        return $this->getMaximalPrice($product, RegularPrice::PRICE_CODE);
    }

    /**
     * @inheritdoc
     */
    public function getRegularPrice(SaleableInterface $product): AmountInterface
    {
        return $product->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getAmount();
    }

    /**
     * Get minimal price from child products
     *
     * @param SaleableInterface $product
     * @param string $code
     * @return AmountInterface
     */
    private function getMinimalPrice(SaleableInterface $product, string $code): AmountInterface
    {
        if (!isset($this->minimalPrice[$code][$product->getId()])) {
            $minimumAmount = null;
            foreach ($this->optionsProvider->getProducts($product) as $variant) {
                $variantAmount = null;
                if ($code === FinalPrice::PRICE_CODE) {
                    $variantAmount = $this->catalogPriceProvider->getMinimalFinalPrice($variant);
                } elseif ($code === RegularPrice::PRICE_CODE) {
                    $variantAmount = $this->catalogPriceProvider->getMinimalRegularPrice($variant);
                }

                if (!$minimumAmount || ($variantAmount->getValue() < $minimumAmount->getValue())) {
                    $minimumAmount = $variantAmount;
                    $this->minimalPrice[$code][$product->getId()] = $variantAmount;
                }
            }
        }

        return $this->minimalPrice[$code][$product->getId()] ?? $this->amountFactory->create(['amount' => null]);
    }

    /**
     * Get maximal price from child products
     *
     * @param SaleableInterface $product
     * @param string $code
     * @return AmountInterface
     */
    private function getMaximalPrice(SaleableInterface $product, string $code): AmountInterface
    {
        if (!isset($this->maximalPrice[$code][$product->getId()])) {
            $maximumAmount = null;
            foreach ($this->optionsProvider->getProducts($product) as $variant) {
                $variantAmount = null;
                if ($code === FinalPrice::PRICE_CODE) {
                    $variantAmount = $this->catalogPriceProvider->getMaximalFinalPrice($variant);
                } elseif ($code === RegularPrice::PRICE_CODE) {
                    $variantAmount = $this->catalogPriceProvider->getMaximalRegularPrice($variant);
                }

                if (!$maximumAmount || ($variantAmount->getValue() > $maximumAmount->getValue())) {
                    $maximumAmount = $variantAmount;
                    $this->maximalPrice[$code][$product->getId()] = $variantAmount;
                }
            }
        }

        return $this->maximalPrice[$code][$product->getId()] ?? $this->amountFactory->create(['amount' => null]);
    }

    /**
     * @inheritDoc
     */
    public function _resetState():void
    {
        $this->minimalPrice[RegularPrice::PRICE_CODE] = [];
        $this->minimalPrice[FinalPrice::PRICE_CODE] = [];
        $this->maximalPrice[RegularPrice::PRICE_CODE] = [];
        $this->maximalPrice[FinalPrice::PRICE_CODE] = [];
        $this->optionsProvider = $this->optionsProviderFactory->create();
    }
}
