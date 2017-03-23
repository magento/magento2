<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
