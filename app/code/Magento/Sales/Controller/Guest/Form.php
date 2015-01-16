<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Guest;

class Form extends \Magento\Framework\App\Action\Action
{
    /**
     * Order view form page
     *
     * @return void
     */
    public function execute()
    {
        if ($this->_objectManager->get('Magento\Customer\Model\Session')->isLoggedIn()) {
            $this->_redirect('customer/account/');
            return;
        }
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Orders and Returns'));
        $this->_objectManager->get('Magento\Sales\Helper\Guest')->getBreadcrumbs();
        $this->_view->renderLayout();
    }
}
