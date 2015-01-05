<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Shopcart;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportAbandonedCsv extends \Magento\Reports\Controller\Adminhtml\Report\Shopcart
{
    /**
     * Export abandoned carts report grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'shopcart_abandoned.csv';
        $content = $this->_view->getLayout()->createBlock(
            'Magento\Reports\Block\Adminhtml\Shopcart\Abandoned\Grid'
        )->getCsvFile();

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
