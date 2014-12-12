<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
