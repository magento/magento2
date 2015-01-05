<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\User\Controller\Adminhtml\User\Role;

class RoleGrid extends \Magento\User\Controller\Adminhtml\User\Role
{
    /**
     * Action for ajax request from grid
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
