<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

use Magento\UrlRewrite\Block\Catalog\Category\Tree;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

class CategoriesJson extends Rewrite
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
                Tree::class
            )->getTreeArray(
                $categoryId,
                true,
                1
            )
        );
    }
}
