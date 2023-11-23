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
use Magento\ConfigurableProduct\Pricing\Price\SpecialPriceBulkResolver;
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
     * @var SpecialPriceBulkResolver
     */
    private SpecialPriceBulkResolver $specialPriceBulkResolver;

    /**
     * @param Context $context
     * @param SaleableInterface $saleableItem
     * @param PriceInterface $price
     * @param RendererPool $rendererPool
     * @param SalableResolverInterface $salableResolver
     * @param MinimalPriceCalculatorInterface $minimalPriceCalculator
     * @param SpecialPriceBulkResolver $specialPriceBulkResolver
     * @param array $data
     */
    public function __construct(
        Context                              $context,
        SaleableInterface                    $saleableItem,
        PriceInterface                       $price,
        RendererPool                         $rendererPool,
        SalableResolverInterface             $salableResolver,
        MinimalPriceCalculatorInterface      $minimalPriceCalculator,
        SpecialPriceBulkResolver             $specialPriceBulkResolver,
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

        $this->specialPriceBulkResolver = $specialPriceBulkResolver;
    }

    /**
     * Define if the special price should be shown
     *
     * @return bool
     * @throws \Exception
     */
    public function hasSpecialPrice()
    {
        if ($this->getData('product_list') === null) {
            return false;
        }

        $specialPriceMap = $this->specialPriceBulkResolver->generateSpecialPriceMap(
            $this->saleableItem->getStoreId(),
            $this->getData('product_list')
        );

        return $specialPriceMap[$this->saleableItem->getId()];
    }
}
