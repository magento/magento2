<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Sales;

/**
 * Class \Magento\Reports\Controller\Adminhtml\Report\Sales\RefreshRecent
 *
 */
class RefreshRecent extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    /**
     * Refresh statistics for last 25 hours
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('refreshRecent', 'report_statistics');
    }
}
