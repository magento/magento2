<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Controller\Adminhtml\Transactions;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\Layout;

class Grid extends \Magento\Sales\Controller\Adminhtml\Transactions
{
    /**
     * Ajax grid action
     *
     * @return Layout
     */
    public function execute()
    {
        return $this->resultLayoutFactory->create();
    }
}
