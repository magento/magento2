<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;

/**
 * Class ExportCouponsCsv
 * @package Magento\SalesRule\Controller\Adminhtml\Promo\Quote
 */
class ExportCouponsCsv extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote
{
    /**
     * Export coupon codes as CSV file
     *
     * @return ResponseInterface|null|void
     * @throws Exception
     */
    public function execute()
    {
        $this->_initRule();
        $rule = $this->_coreRegistry->registry(\Magento\SalesRule\Model\RegistryConstants::CURRENT_SALES_RULE);
        if ($rule->getId()) {
            $fileName = 'coupon_codes.csv';
            $content = $this->_view->getLayout()->createBlock(
                \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid::class
            )->getCsvFile();
            return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
        } else {
            $this->_redirect('sales_rule/*/edit', ['_current' => true]);
            return;
        }
    }
}
