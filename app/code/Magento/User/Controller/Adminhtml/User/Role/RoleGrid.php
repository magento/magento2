<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\User\Role;

/**
 * Class \Magento\User\Controller\Adminhtml\User\Role\RoleGrid
 *
 * @since 2.0.0
 */
class RoleGrid extends \Magento\User\Controller\Adminhtml\User\Role
{
    /**
     * Action for ajax request from grid
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
