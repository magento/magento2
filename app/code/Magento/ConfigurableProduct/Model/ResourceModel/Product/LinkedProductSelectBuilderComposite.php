<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\LinkedProductSelectBuilderInterface;

/**
 * Used in Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProvider
 * to provide queries to select configurable product option with lowest price
 *
 * @see app/code/Magento/ConfigurableProduct/etc/di.xml
 */
class LinkedProductSelectBuilderComposite implements LinkedProductSelectBuilderInterface
{
    /**
     * @var LinkedProductSelectBuilderInterface[]
     */
    private $linkedProductSelectBuilder;

    /**
     * @param LinkedProductSelectBuilderInterface[] $linkedProductSelectBuilder
     */
    public function __construct($linkedProductSelectBuilder)
    {
        $this->linkedProductSelectBuilder = $linkedProductSelectBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function build($productId)
    {
        $selects = [];
        foreach ($this->linkedProductSelectBuilder as $productSelectBuilder) {
            $selects = array_merge($selects, $productSelectBuilder->build($productId));
        }

        return $selects;
    }
}
