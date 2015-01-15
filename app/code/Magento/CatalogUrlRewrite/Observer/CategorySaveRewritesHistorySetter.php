<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Category;
use Magento\Framework\Event\Observer;

class CategorySaveRewritesHistorySetter
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function invoke(Observer $observer)
    {
        /** @var Category $category */
        $category = $observer->getEvent()->getCategory();
        $data = $observer->getEvent()->getRequest()->getPost();

        /**
         * Create Permanent Redirect for old URL key
         */
        if ($category->getId() && isset($data['general']['url_key_create_redirect'])) {
            $category->setData('save_rewrites_history', (bool)$data['general']['url_key_create_redirect']);
        }
    }
}
