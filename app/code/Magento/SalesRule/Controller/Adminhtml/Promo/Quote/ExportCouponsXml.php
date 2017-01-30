<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCouponsXml extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote
{
    /**
     * Export coupon codes as excel xml file
     *
     * @return \Magento\Framework\App\ResponseInterface|null
     */
    public function execute()
    {
        $this->_initRule();
        $rule = $this->_coreRegistry->registry(\Magento\SalesRule\Model\RegistryConstants::CURRENT_SALES_RULE);
        if ($rule->getId()) {
            $fileName = 'coupon_codes.xml';
            $content = $this->_view->getLayout()->createBlock(
                'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid'
            )->getExcelFile(
                $fileName
            );
            return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
        } else {
            $this->_redirect('sales_rule/*/detail', ['_current' => true]);
            return;
        }
    }
}
