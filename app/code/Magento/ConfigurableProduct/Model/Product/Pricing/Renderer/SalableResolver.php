<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product\Pricing\Renderer;

use Magento\Framework\Pricing\SaleableInterface;
use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProviderInterface;

/**
 * A decorator for a salable resolver.
 *
 * Extends functionality of the salable resolver by performing the additional check
 * which is related to configurable products.
 */
class SalableResolver implements SalableResolverInterface
{
    /**
     * @var SalableResolverInterface
     */
    private $salableResolver;

    /**
     * @var LowestPriceOptionsProviderInterface
     */
    private $lowestPriceOptionsProvider;

    /**
     * @param SalableResolverInterface $salableResolver
     * @param LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider
     */
    public function __construct(
        SalableResolverInterface $salableResolver,
        LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider
    ) {
        $this->salableResolver = $salableResolver;
        $this->lowestPriceOptionsProvider = $lowestPriceOptionsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function isSalable(SaleableInterface $salableItem)
    {
        if ($this->lowestPriceOptionsProvider->getProducts($salableItem)) {
            return $this->salableResolver->isSalable($salableItem);
        }

        return false;
    }
}
