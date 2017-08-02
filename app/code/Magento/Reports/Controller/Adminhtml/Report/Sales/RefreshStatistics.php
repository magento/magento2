<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Sales;

/**
 * Class \Magento\Reports\Controller\Adminhtml\Report\Sales\RefreshStatistics
 *
 * @since 2.0.0
 */
class RefreshStatistics extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    /**
     * Refresh report statistics action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_forward('index', 'report_statistics');
    }
}
