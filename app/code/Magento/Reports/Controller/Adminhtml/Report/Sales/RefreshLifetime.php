<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Sales;

class RefreshLifetime extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    /**
     * Refresh statistics for all period
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('refreshLifetime', 'report_statistics');
    }
}
