<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\TaxImportExport\Controller\Adminhtml\Rate;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;

class ExportCsv extends \Magento\TaxImportExport\Controller\Adminhtml\Rate
{
    /**
     * Export rates grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $content = $resultLayout->getLayout()->getChildBlock('adminhtml.tax.rate.grid', 'grid.export');

        return $this->fileFactory->create(
            'rates.csv',
            $content->getCsvFile(),
            DirectoryList::VAR_DIR
        );
    }
}
