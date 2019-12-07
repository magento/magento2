<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite as RewriteAction;

class CmsPageGrid extends RewriteAction implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * Ajax CMS pages grid action
     *
     * @return void
     */
    public function execute()
    {
        $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(\Magento\UrlRewrite\Block\Cms\Page\Grid::class)->toHtml()
        );
    }
}
