<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Review;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCustomerCsv extends \Magento\Reports\Controller\Adminhtml\Report\Review
{
    /**
     * Export review customer report to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $fileName = 'review_customer.csv';
        $exportBlock = $this->_view->getLayout()->getChildBlock(
            'adminhtml.block.report.review.customer.grid',
            'grid.export'
        );
        return $this->_fileFactory->create($fileName, $exportBlock->getCsvFile(), DirectoryList::VAR_DIR);
    }
}
