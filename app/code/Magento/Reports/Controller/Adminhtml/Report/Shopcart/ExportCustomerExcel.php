<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Shopcart;

use Magento\Framework\App\ResponseInterface;

class ExportCustomerExcel extends \Magento\Reports\Controller\Adminhtml\Report\Shopcart
{
    /**
     * Export shopcart customer report to Excel XML format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'shopcart_customer.xml';
        $content = $this->_view->getLayout()->createBlock(
            'Magento\Reports\Block\Adminhtml\Shopcart\Customer\Grid'
        )->getExcelFile(
            $fileName
        );

        return $this->_fileFactory->create($fileName, $content);
    }
}
