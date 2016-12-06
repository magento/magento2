<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Pricing\Render;

use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface;
use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProviderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\View\Element\Template\Context;

class FinalPriceBox extends \Magento\Catalog\Pricing\Render\FinalPriceBox
{
    /**
     * @var LowestPriceOptionsProviderInterface
     */
    private $lowestPriceOptionsProvider;

    /**
     * @param Context $context
     * @param SaleableInterface $saleableItem
     * @param PriceInterface $price
     * @param RendererPool $rendererPool
     * @param ConfigurableOptionsProviderInterface $configurableOptionsProvider
     * @param array $data
     * @param LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Context $context,
        SaleableInterface $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        ConfigurableOptionsProviderInterface $configurableOptionsProvider,
        array $data = [],
        LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider = null
    ) {
        parent::__construct($context, $saleableItem, $price, $rendererPool, $data);
        $this->lowestPriceOptionsProvider = $lowestPriceOptionsProvider ?:
            ObjectManager::getInstance()->get(LowestPriceOptionsProviderInterface::class);
    }

    /**
     * Define if the special price should be shown
     *
     * @return bool
     */
    public function hasSpecialPrice()
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
}
