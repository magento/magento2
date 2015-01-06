<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
