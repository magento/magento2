<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

class Index extends Rewrite implements HttpGetActionInterface
{
    /**
     * Show URL rewrites index page
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_UrlRewrite::urlrewrite');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('URL Rewrites'));
        $this->_view->renderLayout();
    }
}
