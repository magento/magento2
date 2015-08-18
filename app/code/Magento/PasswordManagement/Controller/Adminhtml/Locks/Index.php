<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PasswordManagement\Controller\Adminhtml\Locks;

class Index extends \Magento\PasswordManagement\Controller\Adminhtml\Locks
{
    /**
     * Render page with grid
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_PasswordManagement::system_acl_locks');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Locked Users'));
        $this->_view->renderLayout();
    }
}
