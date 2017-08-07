<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer\Category;

use Magento\Catalog\Model\Layer\ItemCollectionProviderInterface;

/**
 * Class \Magento\Catalog\Model\Layer\Category\ItemCollectionProvider
 *
 */
class ItemCollectionProvider implements ItemCollectionProviderInterface
{
    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getCollection(\Magento\Catalog\Model\Category $category)
    {
        return $category->getProductCollection();
    }
}
