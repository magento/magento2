<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
     */
    protected function _isAllowed()
    {
        return match (strtolower($this->getRequest()->getActionName())) {
            'exportsalescsv', 'exportsalesexcel', 'sales' =>
                $this->_authorization->isAllowed('Magento_Reports::salesroot_sales'),
            'exporttaxcsv', 'exporttaxexcel', 'tax' =>
                $this->_authorization->isAllowed('Magento_Reports::tax'),
            'exportshippingcsv', 'exportshippingexcel', 'shipping' =>
                $this->_authorization->isAllowed('Magento_Reports::shipping'),
            'exportinvoicedcsv', 'exportinvoicedexcel', 'invoiced' =>
                $this->_authorization->isAllowed('Magento_Reports::invoiced'),
            'exportrefundedcsv', 'exportrefundedexcel', 'refunded' =>
                $this->_authorization->isAllowed('Magento_Reports::refunded'),
            'exportcouponscsv', 'exportcouponsexcel', 'coupons' =>
                $this->_authorization->isAllowed('Magento_Reports::coupons'),
            'exportbestsellerscsv', 'exportbestsellersexcel', 'bestsellers' =>
                $this->_authorization->isAllowed('Magento_Reports::bestsellers'),
            default =>
                $this->_authorization->isAllowed('Magento_Reports::salesroot'),
        };
    }
}
