<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

/**
 * Class \Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite\CategoriesJson
 *
 * @since 2.0.0
 */
class CategoriesJson extends \Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite
{
    /**
     * Ajax categories tree loader action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $categoryId = $this->getRequest()->getParam('id', null);
        $this->getResponse()->setBody(
            $this->_objectManager->get(
                \Magento\UrlRewrite\Block\Catalog\Category\Tree::class
            )->getTreeArray(
                $categoryId,
                true,
                1
            )
        );
    }
}
