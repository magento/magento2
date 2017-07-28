<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product;

/**
 * Class \Magento\Catalog\Model\ResourceModel\Product\LinkedProductSelectBuilderComposite
 *
 * @since 2.2.0
 */
class LinkedProductSelectBuilderComposite implements LinkedProductSelectBuilderInterface
{
    /**
     * @var LinkedProductSelectBuilderInterface[]
     * @since 2.2.0
     */
    private $linkedProductSelectBuilder;

    /**
     * @param LinkedProductSelectBuilderInterface[] $linkedProductSelectBuilder
     * @since 2.2.0
     */
    public function __construct($linkedProductSelectBuilder)
    {
        $this->linkedProductSelectBuilder = $linkedProductSelectBuilder;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
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
