<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Controller\Adminhtml\Session;

/**
 * Admin session activity
 * @since 2.1.0
 */
class Activity extends \Magento\Backend\App\Action
{
    /**
     * @return void
     * @since 2.1.0
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Account Activity'));
        $this->_view->renderLayout();
    }
}
