<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\User\Controller\Adminhtml\Locks;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

/**
 * Locks Index action
 */
class Index extends \Magento\User\Controller\Adminhtml\Locks implements HttpGetActionInterface
{
    /**
     * Render page with grid
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_User::system_acl_locks');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Locked Users'));
        $this->_view->renderLayout();
    }
}
