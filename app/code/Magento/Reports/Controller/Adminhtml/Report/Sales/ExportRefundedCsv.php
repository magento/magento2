<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Sales;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportRefundedCsv extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    /**
     * Export refunded report grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'refunded.csv';
        $grid = $this->_view->getLayout()->createBlock('Magento\Reports\Block\Adminhtml\Sales\Refunded\Grid');
        $this->_initReportAction($grid);
        return $this->_fileFactory->create($fileName, $grid->getCsvFile(), DirectoryList::VAR_DIR);
    }
}
