<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Review;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportProductCsv extends \Magento\Reports\Controller\Adminhtml\Report\Review
{
    /**
     * Export review product report to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $fileName = 'review_product.csv';
        $exportBlock = $this->_view->getLayout()->getChildBlock(
            'adminhtml.block.report.review.product.grid',
            'grid.export'
        );
        return $this->_fileFactory->create($fileName, $exportBlock->getCsvFile(), DirectoryList::VAR_DIR);
    }
}
