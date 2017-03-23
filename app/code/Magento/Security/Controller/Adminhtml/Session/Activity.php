<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
