<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Product;

use Magento\Framework\App\ResponseInterface;

class ExportDownloadsCsv extends \Magento\Reports\Controller\Adminhtml\Report\Product
{
    /**
     * Export products downloads report to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'products_downloads.csv';
        $content = $this->_view->getLayout()->createBlock(
            'Magento\Reports\Block\Adminhtml\Product\Downloads\Grid'
        )->setSaveParametersInSession(
            true
        )->getCsv();

        return $this->_fileFactory->create($fileName, $content);
    }
}
