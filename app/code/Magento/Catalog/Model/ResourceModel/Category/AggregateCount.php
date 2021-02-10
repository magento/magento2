<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Category;

use Magento\Catalog\Model\Category;

/**
 * Aggregate count for parent category after deleting child category
 *
 * Class AggregateCount
 */
class AggregateCount
{
    /**
     * Reduces children count for parent categories
     *
     * @param Category $category
     * @return void
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
            $data = ['children_count' => new \Zend_Db_Expr('children_count - 1')];
            $where = ['entity_id IN(?)' => $parentIds];
            $resourceModel->getConnection()->update($resourceModel->getEntityTable(), $data, $where);
        }
    }
}
