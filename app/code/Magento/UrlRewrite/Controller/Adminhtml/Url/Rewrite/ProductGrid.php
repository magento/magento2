<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

use Magento\UrlRewrite\Block\Catalog\Product\Grid;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

class ProductGrid extends Rewrite
{
    /**
     * Ajax products grid action
     *
     * @return void
     */
    public function execute()
    {
        $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(Grid::class)->toHtml()
        );
    }
}
