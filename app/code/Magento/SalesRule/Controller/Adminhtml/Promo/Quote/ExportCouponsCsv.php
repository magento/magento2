<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class \Magento\SalesRule\Controller\Adminhtml\Promo\Quote\ExportCouponsCsv
 *
 * @since 2.0.0
 */
class ExportCouponsCsv extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote
{
    /**
     * Export coupon codes as CSV file
     *
     * @return \Magento\Framework\App\ResponseInterface|null
     * @since 2.0.0
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
            $this->_redirect('sales_rule/*/detail', ['_current' => true]);
            return;
        }
    }
}
