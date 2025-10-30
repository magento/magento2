<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Security\Controller\Adminhtml\Session;

/**
 * Admin session activity
 */
class Activity extends \Magento\Backend\App\Action
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Account Activity'));
        $this->_view->renderLayout();
    }
}
