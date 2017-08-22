<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product;

/**
 * Class \Magento\Catalog\Model\ResourceModel\Product\LinkedProductSelectBuilderComposite
 *
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
