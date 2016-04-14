<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

class Creditmemos extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::creditmemo';

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
}
