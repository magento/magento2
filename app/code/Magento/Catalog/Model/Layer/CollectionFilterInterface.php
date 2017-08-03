<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer;

/**
 * Interface \Magento\Catalog\Model\Layer\CollectionFilterInterface
 *
 * @since 2.0.0
 */
interface CollectionFilterInterface
{
    /**
     * Filter product collection
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param \Magento\Catalog\Model\Category $category
     * @return void
     * @since 2.0.0
     */
    public function filter(
        $collection,
        \Magento\Catalog\Model\Category $category
    );
}
