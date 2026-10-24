<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Reports\Controller\Adminhtml\Report\Product;

use Magento\Backend\Block\Widget\Grid\ExportInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Reports\Controller\Adminhtml\Report\Product;

class ExportSoldExcel extends Product implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Reports::sold';

    /**
     * Export Sold Products report to XML format action
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'products_ordered.xml';
        /** @var ExportInterface $exportBlock */
        $exportBlock = $this->_view->getLayout()->getChildBlock('adminhtml.report.grid', 'grid.export');
        return $this->_fileFactory->create(
            $fileName,
            $exportBlock->getExcelFile($fileName),
            DirectoryList::VAR_DIR
        );
    }
}
