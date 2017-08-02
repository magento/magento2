<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Category;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\CatalogUrlRewrite\Observer\CategorySaveRewritesHistorySetterObserver
 *
 * @since 2.0.0
 */
class CategorySaveRewritesHistorySetterObserver implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Category $category */
        $category = $observer->getEvent()->getCategory();
        $data = $observer->getEvent()->getRequest()->getPostValue();

        /**
         * Create Permanent Redirect for old URL key
         */
        if ($category->getId() && isset($data['url_key_create_redirect'])) {
            $category->setData('save_rewrites_history', (bool)$data['url_key_create_redirect']);
        }
    }
}
