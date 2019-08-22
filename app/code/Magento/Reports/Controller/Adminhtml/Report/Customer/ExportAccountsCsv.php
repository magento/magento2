<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Customer;

use Magento\Backend\Block\Widget\Grid\ExportInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportAccountsCsv extends \Magento\Reports\Controller\Adminhtml\Report\Customer
{
    /**
     * Export new accounts report grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'new_accounts.csv';
        /** @var ExportInterface $exportBlock */
        $exportBlock = $this->_view->getLayout()->getChildBlock('adminhtml.report.grid', 'grid.export');
        return $this->_fileFactory->create(
            $fileName,
            $exportBlock->getCsvFile(),
            DirectoryList::VAR_DIR
        );
    }
}
