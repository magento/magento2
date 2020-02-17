<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product;

/**
 * Collect Select object for list of products
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
     * @inheritdoc
     */
    public function build(int $productId, int $storeId) : array
    {
        $selects = [];
        foreach ($this->linkedProductSelectBuilder as $productSelectBuilder) {
            $selects[] = $productSelectBuilder->build($productId, $storeId);
        }
        $selects = array_merge(...$selects);

        return $selects;
    }
}
