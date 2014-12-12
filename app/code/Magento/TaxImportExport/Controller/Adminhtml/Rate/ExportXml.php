<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TaxImportExport\Controller\Adminhtml\Rate;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportXml extends \Magento\TaxImportExport\Controller\Adminhtml\Rate
{
    /**
     * Export rates grid to XML format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $content = $this->_view->getLayout()->getChildBlock('adminhtml.tax.rate.grid', 'grid.export');
        return $this->fileFactory->create(
            'rates.xml',
            $content->getExcelFile(),
            DirectoryList::VAR_DIR
        );
    }
}
