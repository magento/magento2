<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\Locks;

/**
 * Locks Index action
 */
class Index extends \Magento\User\Controller\Adminhtml\Locks
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
