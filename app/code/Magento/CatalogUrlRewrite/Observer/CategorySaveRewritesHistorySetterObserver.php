<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Category;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CategorySaveRewritesHistorySetterObserver implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Category $category */
        $category = $observer->getEvent()->getCategory();
        $data = $observer->getEvent()->getRequest()->getPostValue();

        /**
         * Create Permanent Redirect for old URL key
         */
        if ($category->getId() && isset($data['general']['url_key_create_redirect'])) {
            $category->setData('save_rewrites_history', (bool)$data['general']['url_key_create_redirect']);
        }
    }
}
