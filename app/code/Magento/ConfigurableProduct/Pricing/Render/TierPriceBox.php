<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Pricing\Render;

use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;
use Magento\Catalog\Pricing\Price\TierPrice;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Responsible for displaying tier price box on configurable product page.
 */
class TierPriceBox extends FinalPriceBox
{
    /**
     * @var ConfigurableOptionsProviderInterface
     */
    private $configurableOptionsProvider;

    /**
     * @param Context $context
     * @param SaleableInterface $saleableItem
     * @param PriceInterface $price
     * @param RendererPool $rendererPool
     * @param SalableResolverInterface $salableResolver
     * @param MinimalPriceCalculatorInterface $minimalPriceCalculator
     * @param ConfigurableOptionsProviderInterface $configurableOptionsProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        SaleableInterface $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        SalableResolverInterface $salableResolver,
        MinimalPriceCalculatorInterface $minimalPriceCalculator,
        ConfigurableOptionsProviderInterface $configurableOptionsProvider,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $saleableItem,
            $price,
            $rendererPool,
            $salableResolver,
            $minimalPriceCalculator,
            $configurableOptionsProvider,
            $data
        );
        $this->configurableOptionsProvider = $configurableOptionsProvider;
    }

    /**
     * @inheritdoc
     */
    public function toHtml()
    {
        // Hide tier price block in case of MSRP or in case when no options with tier price.
        if (!$this->isMsrpPriceApplicable() && $this->isTierPriceApplicable()) {
            return parent::toHtml();
        }
        return '';
    }

    /**
     * Check if at least one of simple products has tier price.
     *
     * @return bool
     */
    private function isTierPriceApplicable()
    {
        foreach ($this->configurableOptionsProvider->getProducts($this->getSaleableItem()) as $simpleProduct) {
            if ($simpleProduct->isSalable() &&
                !empty($simpleProduct->getPriceInfo()->getPrice(TierPrice::PRICE_CODE)->getTierPriceList())
            ) {
                return true;
            }
        }
        return false;
    }
}
