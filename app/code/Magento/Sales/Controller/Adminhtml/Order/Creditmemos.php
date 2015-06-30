<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

class Creditmemos extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Generate credit memos grid for ajax request
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $this->_initOrder();
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::creditmemo');
    }
}
