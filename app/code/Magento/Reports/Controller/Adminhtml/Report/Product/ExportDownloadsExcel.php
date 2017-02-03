<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Product;

use Magento\Framework\App\ResponseInterface;

class ExportDownloadsExcel extends \Magento\Reports\Controller\Adminhtml\Report\Product
{
    /**
     * Check is allowed for report
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Reports::report_products');
    }

    /**
     * Export products downloads report to XLS format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'products_downloads.xml';
        $content = $this->_view->getLayout()->createBlock(
            'Magento\Reports\Block\Adminhtml\Product\Downloads\Grid'
        )->setSaveParametersInSession(
            true
        )->getExcel(
            $fileName
        );

        return $this->_fileFactory->create($fileName, $content);
    }
}
