<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

/**
 * Class \Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite\CmsPageGrid
 *
 * @since 2.0.0
 */
class CmsPageGrid extends \Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite
{
    /**
     * Ajax CMS pages grid action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(\Magento\UrlRewrite\Block\Cms\Page\Grid::class)->toHtml()
        );
    }
}
