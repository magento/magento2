<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sales report admin controller
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Controller\Adminhtml\Report;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class Sales extends AbstractReport
{
    /**
     * Add report/sales breadcrumbs
     *
     * @return $this
     */
    public function _initAction()
    {
        parent::_initAction();
        $this->_addBreadcrumb(__('Sales'), __('Sales'));
        return $this;
    }

    /**
     * Determine if action is allowed for reports module
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _isAllowed()
    {
        switch (strtolower($this->getRequest()->getActionName())) {
            case 'exportsalescsv':
            case 'exportsalesexcel':
            case 'sales':
                return $this->_authorization->isAllowed('Magento_Reports::salesroot_sales');
            case 'exporttaxcsv':
            case 'exporttaxexcel':
            case 'tax':
                return $this->_authorization->isAllowed('Magento_Reports::tax');
            case 'exportshippingcsv':
            case 'exportshippingexcel':
            case 'shipping':
                return $this->_authorization->isAllowed('Magento_Reports::shipping');
            case 'exportinvoicedcsv':
            case 'exportinvoicedexcel':
            case 'invoiced':
                return $this->_authorization->isAllowed('Magento_Reports::invoiced');
            case 'exportrefundedcsv':
            case 'exportrefundedexcel':
            case 'refunded':
                return $this->_authorization->isAllowed('Magento_Reports::refunded');
            case 'exportcouponscsv':
            case 'exportcouponsexcel':
            case 'coupons':
                return $this->_authorization->isAllowed('Magento_Reports::coupons');
            case 'exportbestsellerscsv':
            case 'exportbestsellersexcel':
            case 'bestsellers':
                return $this->_authorization->isAllowed('Magento_Reports::bestsellers');
            default:
                return $this->_authorization->isAllowed('Magento_Reports::salesroot');
        }
    }
}
