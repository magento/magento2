<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Product;

use Magento\Framework\App\ResponseInterface;

/**
 * Class \Magento\Reports\Controller\Adminhtml\Report\Product\ExportDownloadsExcel
 *
 */
class ExportDownloadsExcel extends \Magento\Reports\Controller\Adminhtml\Report\Product
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Reports::report_products';

    /**
     * Export products downloads report to XLS format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'products_downloads.xml';
        $content = $this->_view->getLayout()->createBlock(
            \Magento\Reports\Block\Adminhtml\Product\Downloads\Grid::class
        )->setSaveParametersInSession(
            true
        )->getExcel(
            $fileName
        );

        return $this->_fileFactory->create($fileName, $content);
    }
}
