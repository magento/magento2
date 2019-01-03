<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\User\Role;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\User\Controller\Adminhtml\User\Role as RoleAction;

class RoleGrid extends RoleAction implements HttpGetActionInterface, HttpPostActionInterface
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
