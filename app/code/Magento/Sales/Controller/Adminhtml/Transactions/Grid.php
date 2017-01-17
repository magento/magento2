<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
