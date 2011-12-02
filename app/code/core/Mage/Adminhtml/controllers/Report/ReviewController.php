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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review reports admin controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Report_ReviewController extends Mage_Adminhtml_Controller_Action
{
    public function _initAction()
    {
        $act = $this->getRequest()->getActionName();
        if(!$act)
            $act = 'default';

        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('Mage_Reports_Helper_Data')->__('Reports'), Mage::helper('Mage_Reports_Helper_Data')->__('Reports'))
            ->_addBreadcrumb(Mage::helper('Mage_Reports_Helper_Data')->__('Review'), Mage::helper('Mage_Reports_Helper_Data')->__('Reviews'));
        return $this;
    }

    public function customerAction()
    {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Reviews'))
             ->_title($this->__('Customer Reviews'));

        $this->_initAction()
            ->_setActiveMenu('report/review/customer')
            ->_addBreadcrumb(Mage::helper('Mage_Reports_Helper_Data')->__('Customers Report'), Mage::helper('Mage_Reports_Helper_Data')->__('Customers Report'))
            ->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Report_Review_Customer'))
            ->renderLayout();
    }

    /**
     * Export review customer report to CSV format
     */
    public function exportCustomerCsvAction()
    {
        $fileName   = 'review_customer.csv';
        $content    = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Report_Review_Customer_Grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export review customer report to Excel XML format
     */
    public function exportCustomerExcelAction()
    {
        $fileName   = 'review_customer.xml';
        $content    = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Report_Review_Customer_Grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function productAction()
    {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Reviews'))
             ->_title($this->__('Product Reviews'));

        $this->_initAction()
            ->_setActiveMenu('report/review/product')
            ->_addBreadcrumb(Mage::helper('Mage_Reports_Helper_Data')->__('Products Report'), Mage::helper('Mage_Reports_Helper_Data')->__('Products Report'))
            ->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Report_Review_Product'))
            ->renderLayout();
    }

    /**
     * Export review product report to CSV format
     */
    public function exportProductCsvAction()
    {
        $fileName   = 'review_product.csv';
        $content    = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Report_Review_Product_Grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export review product report to Excel XML format
     */
    public function exportProductExcelAction()
    {
        $fileName   = 'review_product.xml';
        $content    = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Report_Review_Product_Grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function productDetailAction()
    {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Reviews'))
             ->_title($this->__('Product Reviews'))
             ->_title($this->__('Details'));

        $this->_initAction()
            ->_setActiveMenu('report/review/productDetail')
            ->_addBreadcrumb(Mage::helper('Mage_Reports_Helper_Data')->__('Products Report'), Mage::helper('Mage_Reports_Helper_Data')->__('Products Report'))
            ->_addBreadcrumb(Mage::helper('Mage_Reports_Helper_Data')->__('Product Reviews'), Mage::helper('Mage_Reports_Helper_Data')->__('Product Reviews'))
            ->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Report_Review_Detail'))
            ->renderLayout();
    }

    /**
     * Export review product detail report to CSV format
     */
    public function exportProductDetailCsvAction()
    {
        $fileName   = 'review_product_detail.csv';
        $content    = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Report_Review_Detail_Grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export review product detail report to ExcelXML format
     */
    public function exportProductDetailExcelAction()
    {
        $fileName   = 'review_product_detail.xml';
        $content    = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Report_Review_Detail_Grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'customer':
                return Mage::getSingleton('Mage_Admin_Model_Session')->isAllowed('report/review/customer');
                break;
            case 'product':
                return Mage::getSingleton('Mage_Admin_Model_Session')->isAllowed('report/review/product');
                break;
            default:
                return Mage::getSingleton('Mage_Admin_Model_Session')->isAllowed('report/review');
                break;
        }
    }
}
