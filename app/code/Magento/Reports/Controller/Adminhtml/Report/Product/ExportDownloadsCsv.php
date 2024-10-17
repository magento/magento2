<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Controller\Adminhtml\Report\Product;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Reports\Controller\Adminhtml\Report\Product;

/**
 * Exporting list of product in CVS format.
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class ExportDownloadsCsv extends Product
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Reports::report_products';

    /**
     * Export products downloads report to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'products_downloads.csv';
        $content = $this->_view->getLayout()->createBlock(
            \Magento\Reports\Block\Adminhtml\Product\Downloads\Grid::class
        )->setSaveParametersInSession(
            true
        )->getCsv();

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
