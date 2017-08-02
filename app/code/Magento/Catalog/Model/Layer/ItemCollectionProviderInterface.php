<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Layer;

/**
 * Interface \Magento\Catalog\Model\Layer\ItemCollectionProviderInterface
 *
 * @since 2.0.0
 */
interface ItemCollectionProviderInterface
{
    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @since 2.0.0
     */
    public function getCollection(\Magento\Catalog\Model\Category $category);
}
