<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Shopcart;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportProductCsv extends \Magento\Reports\Controller\Adminhtml\Report\Shopcart
{
    /**
     * Export products report grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'shopcart_product.csv';
        $content = $this->_view->getLayout()->createBlock(
            'Magento\Reports\Block\Adminhtml\Shopcart\Product\Grid'
        )->getCsvFile();

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
