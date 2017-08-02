<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Category;

use Magento\Catalog\Model\Category;

/**
 * Class AggregateCount
 * @since 2.1.0
 */
class AggregateCount
{
    /**
     * @param Category $category
     * @return void
     * @since 2.1.0
     */
    public function processDelete(Category $category)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category $resourceModel */
        $resourceModel = $category->getResource();
        /**
         * Update children count for all parent categories
         */
        $parentIds = $category->getParentIds();
        if ($parentIds) {
            $childDecrease = $category->getChildrenCount() + 1;
            // +1 is itself
            $data = ['children_count' => new \Zend_Db_Expr('children_count - ' . $childDecrease)];
            $where = ['entity_id IN(?)' => $parentIds];
            $resourceModel->getConnection()->update($resourceModel->getEntityTable(), $data, $where);
        }
    }
}
