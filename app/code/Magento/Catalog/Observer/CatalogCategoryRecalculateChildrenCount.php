<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Catalog\Model\Category;

class CatalogCategoryRecalculateChildrenCount implements ObserverInterface
{
    /**
     * Recalculate children count for category
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $observer->getEvent()->getData('category');

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
