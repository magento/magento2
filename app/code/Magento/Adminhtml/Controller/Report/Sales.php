<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales report admin controller
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Controller\Report;

class Sales extends \Magento\Adminhtml\Controller\Report\AbstractReport
{
    /**
     * Add report/sales breadcrumbs
     *
     * @return \Magento\Adminhtml\Controller\Report\Sales
     */
    public function _initAction()
    {
        parent::_initAction();
        $this->_addBreadcrumb(__('Sales'), __('Sales'));
        return $this;
    }

    public function salesAction()
    {
        $this->_title(__('Sales Report'));

        $this->_showLastExecutionTime(\Magento\Reports\Model\Flag::REPORT_ORDER_FLAG_CODE, 'sales');

        $this->_initAction()
            ->_setActiveMenu('Magento_Reports::report_salesroot_sales')
            ->_addBreadcrumb(__('Sales Report'), __('Sales Report'));

        $gridBlock = $this->getLayout()->getBlock('report_sales_sales.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));

        $this->renderLayout();
    }

    public function bestsellersAction()
    {
        $this->_title(__('Best Sellers Report'));

        $this->_showLastExecutionTime(\Magento\Reports\Model\Flag::REPORT_BESTSELLERS_FLAG_CODE, 'bestsellers');

        $this->_initAction()
            ->_setActiveMenu('Magento_Reports::report_products_bestsellers')
            ->_addBreadcrumb(__('Products Bestsellers Report'), __('Products Bestsellers Report'));

        $gridBlock = $this->getLayout()->getBlock('report_sales_bestsellers.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));

        $this->renderLayout();
    }

    /**
     * Export bestsellers report grid to CSV format
     */
    public function exportBestsellersCsvAction()
    {
        $fileName   = 'bestsellers.csv';
        $grid       = $this->getLayout()->createBlock('Magento\Adminhtml\Block\Report\Sales\Bestsellers\Grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     * Export bestsellers report grid to Excel XML format
     */
    public function exportBestsellersExcelAction()
    {
        $fileName   = 'bestsellers.xml';
        $grid       = $this->getLayout()->createBlock('Magento\Adminhtml\Block\Report\Sales\Bestsellers\Grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    /**
     * Refresh statistics for last 25 hours
     *
     * @return \Magento\Adminhtml\Controller\Report\Sales
     */
    public function refreshRecentAction()
    {
        return $this->_forward('refreshRecent', 'report_statistics');
    }

    /**
     * Refresh statistics for all period
     *
     * @return \Magento\Adminhtml\Controller\Report\Sales
     */
    public function refreshLifetimeAction()
    {
        return $this->_forward('refreshLifetime', 'report_statistics');
    }

    /**
     * Export sales report grid to CSV format
     */
    public function exportSalesCsvAction()
    {
        $fileName   = 'sales.csv';
        $grid       = $this->getLayout()->createBlock('Magento\Adminhtml\Block\Report\Sales\Sales\Grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     * Export sales report grid to Excel XML format
     */
    public function exportSalesExcelAction()
    {
        $fileName   = 'sales.xml';
        $grid       = $this->getLayout()->createBlock('Magento\Adminhtml\Block\Report\Sales\Sales\Grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    public function taxAction()
    {
        $this->_title(__('Tax Report'));

        $this->_showLastExecutionTime(\Magento\Reports\Model\Flag::REPORT_TAX_FLAG_CODE, 'tax');

        $this->_initAction()
            ->_setActiveMenu('Magento_Reports::report_salesroot_tax')
            ->_addBreadcrumb(__('Tax'), __('Tax'));

        $gridBlock = $this->getLayout()->getBlock('report_sales_tax.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));

        $this->renderLayout();
    }

    /**
     * Export tax report grid to CSV format
     */
    public function exportTaxCsvAction()
    {
        $fileName   = 'tax.csv';
        $grid       = $this->getLayout()->createBlock('Magento\Adminhtml\Block\Report\Sales\Tax\Grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     * Export tax report grid to Excel XML format
     */
    public function exportTaxExcelAction()
    {
        $fileName   = 'tax.xml';
        $grid       = $this->getLayout()->createBlock('Magento\Adminhtml\Block\Report\Sales\Tax\Grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    public function shippingAction()
    {
        $this->_title(__('Shipping Report'));

        $this->_showLastExecutionTime(\Magento\Reports\Model\Flag::REPORT_SHIPPING_FLAG_CODE, 'shipping');

        $this->_initAction()
            ->_setActiveMenu('Magento_Reports::report_salesroot_shipping')
            ->_addBreadcrumb(__('Shipping'), __('Shipping'));

        $gridBlock = $this->getLayout()->getBlock('report_sales_shipping.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));

        $this->renderLayout();
    }

    /**
     * Export shipping report grid to CSV format
     */
    public function exportShippingCsvAction()
    {
        $fileName   = 'shipping.csv';
        $grid       = $this->getLayout()->createBlock('Magento\Adminhtml\Block\Report\Sales\Shipping\Grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     * Export shipping report grid to Excel XML format
     */
    public function exportShippingExcelAction()
    {
        $fileName   = 'shipping.xml';
        $grid       = $this->getLayout()->createBlock('Magento\Adminhtml\Block\Report\Sales\Shipping\Grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    public function invoicedAction()
    {
        $this->_title(__('Invoice Report'));

        $this->_showLastExecutionTime(\Magento\Reports\Model\Flag::REPORT_INVOICE_FLAG_CODE, 'invoiced');

        $this->_initAction()
            ->_setActiveMenu('Magento_Reports::report_salesroot_invoiced')
            ->_addBreadcrumb(__('Total Invoiced'), __('Total Invoiced'));

        $gridBlock = $this->getLayout()->getBlock('report_sales_invoiced.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));

        $this->renderLayout();
    }

    /**
     * Export invoiced report grid to CSV format
     */
    public function exportInvoicedCsvAction()
    {
        $fileName   = 'invoiced.csv';
        $grid       = $this->getLayout()->createBlock('Magento\Adminhtml\Block\Report\Sales\Invoiced\Grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     * Export invoiced report grid to Excel XML format
     */
    public function exportInvoicedExcelAction()
    {
        $fileName   = 'invoiced.xml';
        $grid       = $this->getLayout()->createBlock('Magento\Adminhtml\Block\Report\Sales\Invoiced\Grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    public function refundedAction()
    {
        $this->_title(__('Refunds Report'));

        $this->_showLastExecutionTime(\Magento\Reports\Model\Flag::REPORT_REFUNDED_FLAG_CODE, 'refunded');

        $this->_initAction()
            ->_setActiveMenu('Magento_Reports::report_salesroot_refunded')
            ->_addBreadcrumb(__('Total Refunded'), __('Total Refunded'));

        $gridBlock = $this->getLayout()->getBlock('report_sales_refunded.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));

        $this->renderLayout();
    }

    /**
     * Export refunded report grid to CSV format
     */
    public function exportRefundedCsvAction()
    {
        $fileName   = 'refunded.csv';
        $grid       = $this->getLayout()->createBlock('Magento\Adminhtml\Block\Report\Sales\Refunded\Grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     * Export refunded report grid to Excel XML format
     */
    public function exportRefundedExcelAction()
    {
        $fileName   = 'refunded.xml';
        $grid       = $this->getLayout()->createBlock('Magento\Adminhtml\Block\Report\Sales\Refunded\Grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    public function couponsAction()
    {
        $this->_title(__('Coupons Report'));

        $this->_showLastExecutionTime(\Magento\Reports\Model\Flag::REPORT_COUPONS_FLAG_CODE, 'coupons');

        $this->_initAction()
            ->_setActiveMenu('Magento_Reports::report_salesroot_coupons')
            ->_addBreadcrumb(__('Coupons'), __('Coupons'));

        $gridBlock = $this->getLayout()->getBlock('report_sales_coupons.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));

        $this->renderLayout();
    }

    /**
     * Export coupons report grid to CSV format
     */
    public function exportCouponsCsvAction()
    {
        $fileName   = 'coupons.csv';
        $grid       = $this->getLayout()->createBlock('Magento\Adminhtml\Block\Report\Sales\Coupons\Grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     * Export coupons report grid to Excel XML format
     */
    public function exportCouponsExcelAction()
    {
        $fileName   = 'coupons.xml';
        $grid       = $this->getLayout()->createBlock('Magento\Adminhtml\Block\Report\Sales\Coupons\Grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    public function refreshStatisticsAction()
    {
        return $this->_forward('index', 'report_statistics');
    }

    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'sales':
                return $this->_authorization->isAllowed('Magento_Reports::salesroot_sales');
                break;
            case 'tax':
                return $this->_authorization->isAllowed('Magento_Reports::tax');
                break;
            case 'shipping':
                return $this->_authorization->isAllowed('Magento_Reports::shipping');
                break;
            case 'invoiced':
                return $this->_authorization->isAllowed('Magento_Reports::invoiced');
                break;
            case 'refunded':
                return $this->_authorization->isAllowed('Magento_Reports::refunded');
                break;
            case 'coupons':
                return $this->_authorization->isAllowed('Magento_Reports::coupons');
                break;
            case 'shipping':
                return $this->_authorization->isAllowed('Magento_Reports::shipping');
                break;
            case 'bestsellers':
                return $this->_authorization->isAllowed('Magento_Reports::bestsellers');
                break;
            default:
                return $this->_authorization->isAllowed('Magento_Reports::salesroot');
                break;
        }
    }
}
