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
 * Class \Magento\Reports\Controller\Adminhtml\Report\Sales\ExportRefundedExcel
 *
 * @since 2.0.0
 */
class ExportRefundedExcel extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    /**
     * Export refunded report grid to Excel XML format
     *
     * @return ResponseInterface
     * @since 2.0.0
     */
    public function execute()
    {
        $fileName = 'refunded.xml';
        $grid = $this->_view->getLayout()->createBlock(\Magento\Reports\Block\Adminhtml\Sales\Refunded\Grid::class);
        $this->_initReportAction($grid);
        return $this->_fileFactory->create($fileName, $grid->getExcelFile($fileName), DirectoryList::VAR_DIR);
    }
}
