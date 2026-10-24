<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\User\Controller\Adminhtml\User;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Index extends \Magento\User\Controller\Adminhtml\User implements HttpGetActionInterface
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Users'));
        $this->_view->renderLayout();
    }
}
