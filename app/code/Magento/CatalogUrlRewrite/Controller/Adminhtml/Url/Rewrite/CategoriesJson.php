<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Controller\Adminhtml\Url\Rewrite;

class CategoriesJson extends \Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite
{
    /**
     * Ajax categories tree loader action
     *
     * @return void
     */
    public function execute()
    {
        $categoryId = $this->getRequest()->getParam('id', null);
        $this->getResponse()->setBody(
            $this->_objectManager->get(
                'Magento\CatalogUrlRewrite\Block\Category\Tree'
            )->getTreeArray(
                $categoryId,
                true,
                1
            )
        );
    }
}
