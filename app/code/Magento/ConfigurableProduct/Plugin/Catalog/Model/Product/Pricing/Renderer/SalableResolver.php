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
<<<<<<< HEAD
     * Performs an additional check whether given configurable product has
     * at least one configuration in-stock.
=======
     * Performs an additional check whether given configurable product has at least one configuration in-stock.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
            $result = $salableItem->isSalable();
        }

        return $result;
    }
}
