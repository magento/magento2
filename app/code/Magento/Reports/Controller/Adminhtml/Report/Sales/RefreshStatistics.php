<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Sales;

class RefreshStatistics extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    /**
     * Refresh report statistics action
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('index', 'report_statistics');
    }
}
