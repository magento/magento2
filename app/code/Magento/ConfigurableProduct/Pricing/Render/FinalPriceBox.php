<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Pricing\Render;

use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class for final_price box rendering
 */
class FinalPriceBox extends \Magento\Catalog\Pricing\Render\FinalPriceBox
{
    /**
     * @var ConfigurableOptionsProviderInterface
     */
    private ConfigurableOptionsProviderInterface $configurableOptionsProvider;

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
        Context                              $context,
        SaleableInterface                    $saleableItem,
        PriceInterface                       $price,
        RendererPool                         $rendererPool,
        SalableResolverInterface             $salableResolver,
        MinimalPriceCalculatorInterface      $minimalPriceCalculator,
        ConfigurableOptionsProviderInterface $configurableOptionsProvider,
        array                                $data = []
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

        $this->configurableOptionsProvider = $configurableOptionsProvider;
    }

    /**
     * Define if the special price should be shown
     *
     * @return bool
     * @throws \Exception
     */
    public function hasSpecialPrice()
    {
        if ($this->isProductList()) {
            if (!$this->getData('special_price_map')) {
                return false;
            }

            return (bool)$this->getData('special_price_map')[$this->saleableItem->getId()];
        } else {
            $product = $this->getSaleableItem();
            foreach ($this->configurableOptionsProvider->getProducts($product) as $subProduct) {
                $regularPrice = $subProduct->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getValue();
                $finalPrice = $subProduct->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue();
                if ($finalPrice < $regularPrice) {
                    return true;
                }
            }
            return false;
        }
    }
}
