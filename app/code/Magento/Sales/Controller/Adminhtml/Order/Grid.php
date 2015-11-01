<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

class Grid extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Order grid
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function executeInternal()
    {
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
