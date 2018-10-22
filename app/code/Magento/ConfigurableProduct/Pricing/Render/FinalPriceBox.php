<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Pricing\Render;

use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface;
use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProviderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\StockStatus;

class FinalPriceBox extends \Magento\Catalog\Pricing\Render\FinalPriceBox
{
    /**
     * @var LowestPriceOptionsProviderInterface
     */
    private $lowestPriceOptionsProvider;

    /**
     * @var StockStatus
     */
    private $stockStatus;

    /**
     * @param Context $context
     * @param SaleableInterface $saleableItem
     * @param PriceInterface $price
     * @param RendererPool $rendererPool
     * @param ConfigurableOptionsProviderInterface $configurableOptionsProvider
     * @param array $data
     * @param LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider
     * @param SalableResolverInterface|null $salableResolver
     * @param MinimalPriceCalculatorInterface|null $minimalPriceCalculator
     * @param StockStatus $stockStatus
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Context $context,
        SaleableInterface $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        ConfigurableOptionsProviderInterface $configurableOptionsProvider,
        array $data = [],
        LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider = null,
        SalableResolverInterface $salableResolver = null,
        MinimalPriceCalculatorInterface $minimalPriceCalculator = null,
        StockStatus $stockStatus = null
    ) {
        parent::__construct(
            $context,
            $saleableItem,
            $price,
            $rendererPool,
            $data,
            $salableResolver,
            $minimalPriceCalculator
        );
        $this->lowestPriceOptionsProvider = $lowestPriceOptionsProvider ?:
            ObjectManager::getInstance()->get(LowestPriceOptionsProviderInterface::class);
        $this->stockStatus = $stockStatus ?: ObjectManager::getInstance()->get(StockStatus::class);
    }

    /**
     * Define if the special price should be shown
     *
     * @return bool
     */
    public function hasSpecialPrice(): bool
    {
        $product = $this->getSaleableItem();
        foreach ($this->lowestPriceOptionsProvider->getProducts($product) as $subProduct) {
            $regularPrice = $subProduct->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getValue();
            $finalPrice = $subProduct->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue();
            if ($finalPrice < $regularPrice) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function isApplySalableCheck(SaleableInterface $salableItem): bool
    {
        return !$this->stockStatus->isAllChildOutOfStock($salableItem->getId());
    }
}
