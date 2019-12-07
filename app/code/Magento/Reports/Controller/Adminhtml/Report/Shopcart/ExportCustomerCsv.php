<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Controller\Adminhtml\Report\Shopcart;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Reports\Controller\Adminhtml\Report\Shopcart\Customer as ShopCartCustomer;

/**
 * Class \Magento\Reports\Controller\Adminhtml\Report\Shopcart\ExportCustomerCsv
 */
class ExportCustomerCsv extends ShopCartCustomer implements HttpGetActionInterface
{
    /**
     * Export shopcart customer report to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'shopcart_customer.csv';
        $content = $this->_view->getLayout()->createBlock(
            \Magento\Reports\Block\Adminhtml\Shopcart\Customer\Grid::class
        )->getCsvFile();

        return $this->_fileFactory->create($fileName, $content);
    }
}
