<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Customer;

use Magento\Backend\Block\Widget\Grid\ExportInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportOrdersExcel extends \Magento\Reports\Controller\Adminhtml\Report\Customer
{
    /**
     * Export customers most ordered report to Excel XML format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'customers_orders.xml';
        /** @var ExportInterface $exportBlock */
        $exportBlock = $this->_view->getLayout()->getChildBlock('adminhtml.report.grid', 'grid.export');
        return $this->_fileFactory->create(
            $fileName,
            $exportBlock->getExcelFile($fileName),
            DirectoryList::VAR_DIR
        );
    }
}
