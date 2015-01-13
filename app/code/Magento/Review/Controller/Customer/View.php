<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Customer;

class View extends \Magento\Review\Controller\Customer
{
    /**
     * Render review details
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        if ($navigationBlock = $this->_view->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('review/customer');
        }
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Review Details'));
        $this->_view->renderLayout();
    }
}
