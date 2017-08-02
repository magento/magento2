<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;

/**
 * Class \Magento\Sales\Controller\Adminhtml\Order\Transactions
 *
 * @since 2.0.0
 */
class Transactions extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Order transactions grid ajax action
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
