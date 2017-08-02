<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Sales;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class \Magento\Reports\Controller\Adminhtml\Report\Sales\ExportRefundedCsv
 *
 * @since 2.0.0
 */
class ExportRefundedCsv extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    /**
     * Export refunded report grid to CSV format
     *
     * @return ResponseInterface
     * @since 2.0.0
     */
    public function execute()
    {
        $fileName = 'refunded.csv';
        $grid = $this->_view->getLayout()->createBlock(\Magento\Reports\Block\Adminhtml\Sales\Refunded\Grid::class);
        $this->_initReportAction($grid);
        return $this->_fileFactory->create($fileName, $grid->getCsvFile(), DirectoryList::VAR_DIR);
    }
}
