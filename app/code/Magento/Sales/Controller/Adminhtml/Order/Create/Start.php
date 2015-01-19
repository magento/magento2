<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Create;

use Magento\Backend\App\Action;

class Start extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Start order create action
     *
     * @return void
     */
    public function execute()
    {
        $this->_getSession()->clearStorage();
        $this->_redirect('sales/*', ['customer_id' => $this->getRequest()->getParam('customer_id')]);
    }
}
