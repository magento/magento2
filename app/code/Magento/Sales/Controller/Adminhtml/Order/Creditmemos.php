<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

/**
 * Class \Magento\Sales\Controller\Adminhtml\Order\Creditmemos
 *
 * @since 2.0.0
 */
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
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_initOrder();
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
