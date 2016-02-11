<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Catalog\Model\Category;

class CatalogCategoryDeleteChildren implements ObserverInterface
{
    /**
     * Delete children of category
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
        $resourceModel->deleteChildren($category);
    }
}
