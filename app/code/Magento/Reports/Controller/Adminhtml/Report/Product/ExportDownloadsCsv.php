<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Product;

use Magento\Framework\App\ResponseInterface;

/**
 * Class \Magento\Reports\Controller\Adminhtml\Report\Product\ExportDownloadsCsv
 *
 * @since 2.0.0
 */
class ExportDownloadsCsv extends \Magento\Reports\Controller\Adminhtml\Report\Product
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Reports::report_products';

    /**
     * Export products downloads report to CSV format
     *
     * @return ResponseInterface
     * @since 2.0.0
     */
    public function execute()
    {
        $fileName = 'products_downloads.csv';
        $content = $this->_view->getLayout()->createBlock(
            \Magento\Reports\Block\Adminhtml\Product\Downloads\Grid::class
        )->setSaveParametersInSession(
            true
        )->getCsv();

        return $this->_fileFactory->create($fileName, $content);
    }
}
