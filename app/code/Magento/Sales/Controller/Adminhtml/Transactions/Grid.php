<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Transactions;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\Layout;

/**
 * Class \Magento\Sales\Controller\Adminhtml\Transactions\Grid
 *
 */
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
