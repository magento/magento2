<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

class Shipments extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Generate shipments grid for ajax request
     *
     * @return \Magento\Framework\View\Result\Layout|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $order = $this->_initOrder();
        if ($order) {
            $resultLayout = $this->resultLayoutFactory->create();
            return $resultLayout;
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/*/');
        return $resultRedirect;
    }
}
