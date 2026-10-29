<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\User\Controller\Adminhtml\User;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\User\Controller\Adminhtml\User as UserAction;

class RoleGrid extends UserAction implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
