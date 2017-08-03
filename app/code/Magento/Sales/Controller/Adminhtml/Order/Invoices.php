<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

/**
 * Class \Magento\Sales\Controller\Adminhtml\Order\Invoices
 *
 * @since 2.0.0
 */
class Invoices extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Generate invoices grid for ajax request
     *
     * @return \Magento\Framework\View\Result\Layout
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_initOrder();
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
