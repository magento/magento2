<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product;

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
        $select = [];
        foreach ($this->linkedProductSelectBuilder as $productSelectBuilder) {
            $select = array_merge($select, $productSelectBuilder->build($productId));
        }

        return $select;
    }
}
