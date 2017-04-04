<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Review;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportProductDetailCsv extends \Magento\Reports\Controller\Adminhtml\Report\Review
{
    /**
     * Export review product detail report to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'review_product_detail.csv';
        $content = $this->_view->getLayout()->createBlock(
            \Magento\Reports\Block\Adminhtml\Review\Detail\Grid::class
        )->getCsv();

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
