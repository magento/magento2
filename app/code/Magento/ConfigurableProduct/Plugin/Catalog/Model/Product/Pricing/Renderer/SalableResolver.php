<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Plugin\Catalog\Model\Product\Pricing\Renderer;

use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProviderInterface;

/**
 * A plugin for a salable resolver.
 */
class SalableResolver
{
    /**
     * @var LowestPriceOptionsProviderInterface
     */
    private $lowestPriceOptionsProvider;

    /**
     * @param LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider
     */
    public function __construct(
        LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider
    ) {
        $this->lowestPriceOptionsProvider = $lowestPriceOptionsProvider;
    }

    /**
     * Performs an additional check whether given configurable product has
     * at least one configuration in-stock.
     *
     * @param \Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolver $subject
     * @param bool $result
     * @param \Magento\Framework\Pricing\SaleableInterface $salableItem
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsSalable(
        \Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolver $subject,
        $result,
        \Magento\Framework\Pricing\SaleableInterface $salableItem
    ) {
        if ($salableItem->getTypeId() == 'configurable' && $result) {
            if (!$this->lowestPriceOptionsProvider->getProducts($salableItem)) {
                $result = false;
            }
        }

        return $result;
    }
}
