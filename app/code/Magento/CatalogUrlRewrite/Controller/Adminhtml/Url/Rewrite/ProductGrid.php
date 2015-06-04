<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Controller\Adminhtml\Url\Rewrite;

class ProductGrid extends \Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite
{
    /**
     * Ajax products grid action
     *
     * @return void
     */
    public function execute()
    {
        $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock('Magento\CatalogUrlRewrite\Block\Product\Grid')->toHtml()
        );
    }
}
