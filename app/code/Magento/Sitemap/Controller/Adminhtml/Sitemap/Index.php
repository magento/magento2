<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sitemap\Controller\Adminhtml\Sitemap;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action;

class Index extends \Magento\Sitemap\Controller\Adminhtml\Sitemap implements HttpGetActionInterface
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Site Map'));
        $this->_view->renderLayout();
    }
}
