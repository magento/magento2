<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

/**
 * Class \Magento\Sales\Controller\Adminhtml\Order\Grid
 *
 */
class Grid extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Order grid
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
