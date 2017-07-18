<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\User\Role;

class Index extends \Magento\User\Controller\Adminhtml\User\Role
{
    /**
     * Show grid with roles existing in systems
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Roles'));
        $this->_view->renderLayout();
    }
}
