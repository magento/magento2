<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TaxImportExport\Controller\Adminhtml\Rate;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class \Magento\TaxImportExport\Controller\Adminhtml\Rate\ExportCsv
 *
 * @since 2.0.0
 */
class ExportCsv extends \Magento\TaxImportExport\Controller\Adminhtml\Rate
{
    /**
     * Export rates grid to CSV format
     *
     * @return ResponseInterface
     * @since 2.0.0
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
