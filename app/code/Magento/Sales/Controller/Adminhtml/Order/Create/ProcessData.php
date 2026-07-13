<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Create;

use Magento\Framework\App\Action\HttpPostActionInterface;

class ProcessData extends \Magento\Sales\Controller\Adminhtml\Order\Create implements HttpPostActionInterface
{
    /**
     * Process data and display index page
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $this->_initSession();
        $this->_processData();
        return $this->resultRedirectFactory->create()->setPath('sales/*');
    }
}
