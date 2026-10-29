<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
