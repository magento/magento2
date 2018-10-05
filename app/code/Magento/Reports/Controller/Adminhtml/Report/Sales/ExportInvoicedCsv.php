<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Sales;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;

class ExportInvoicedCsv extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    /**
     * Export invoiced report grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'invoiced.csv';
        $grid = $this->_view->getLayout()->createBlock(\Magento\Reports\Block\Adminhtml\Sales\Invoiced\Grid::class);
        $this->_initReportAction($grid);
        return $this->_fileFactory->create($fileName, $grid->getCsvFile(), DirectoryList::VAR_DIR);
    }
}
