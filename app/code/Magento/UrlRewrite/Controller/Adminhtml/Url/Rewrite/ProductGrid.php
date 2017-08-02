<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

/**
 * Class \Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite\ProductGrid
 *
 * @since 2.0.0
 */
class ProductGrid extends \Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite
{
    /**
     * Ajax products grid action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(\Magento\UrlRewrite\Block\Catalog\Product\Grid::class)->toHtml()
        );
    }
}
