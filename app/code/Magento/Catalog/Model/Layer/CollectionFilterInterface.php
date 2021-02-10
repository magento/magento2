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
 * @api
 */
interface CollectionFilterInterface
{
    /**
     * Filter product collection
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param \Magento\Catalog\Model\Category $category
     * @return void
     */
    public function filter(
        $collection,
        \Magento\Catalog\Model\Category $category
    );
}
