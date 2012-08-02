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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *
 * Customer reports admin controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Report_CustomerController extends Mage_Adminhtml_Controller_Action
{
    public function _initAction()
    {
        $act = $this->getRequest()->getActionName();
        if(!$act)
            $act = 'default';

        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('Mage_Reports_Helper_Data')->__('Reports'), Mage::helper('Mage_Reports_Helper_Data')->__('Reports'))
            ->_addBreadcrumb(Mage::helper('Mage_Reports_Helper_Data')->__('Customers'), Mage::helper('Mage_Reports_Helper_Data')->__('Customers'));
        return $this;
    }

    public function accountsAction()
    {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Customers'))
             ->_title($this->__('New Accounts'));

        $this->_initAction()
            ->_setActiveMenu('Mage_Reports::report_customers_accounts')
            ->_addBreadcrumb(Mage::helper('Mage_Adminhtml_Helper_Data')->__('New Accounts'), Mage::helper('Mage_Adminhtml_Helper_Data')->__('New Accounts'))
            ->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Report_Customer_Accounts'))
            ->renderLayout();
    }

    /**
     * Export new accounts report grid to CSV format
     */
    public function exportAccountsCsvAction()
    {
        $fileName   = 'new_accounts.csv';
        $content    = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Report_Customer_Accounts_Grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export new accounts report grid to Excel XML format
     */
    public function exportAccountsExcelAction()
    {
        $fileName   = 'accounts.xml';
        $content    = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Report_Customer_Accounts_Grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function ordersAction()
    {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Customers'))
             ->_title($this->__('Customers by Number of Orders'));

        $this->_initAction()
            ->_setActiveMenu('Mage_Reports::report_customers_orders')
            ->_addBreadcrumb(Mage::helper('Mage_Reports_Helper_Data')->__('Customers by Number of Orders'),
                Mage::helper('Mage_Reports_Helper_Data')->__('Customers by Number of Orders'))
            ->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Report_Customer_Orders'))
            ->renderLayout();
    }

    /**
     * Export customers most ordered report to CSV format
     */
    public function exportOrdersCsvAction()
    {
        $fileName   = 'customers_orders.csv';
        $content    = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Report_Customer_Orders_Grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export customers most ordered report to Excel XML format
     */
    public function exportOrdersExcelAction()
    {
        $fileName   = 'customers_orders.xml';
        $content    = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Report_Customer_Orders_Grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function totalsAction()
    {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Customers'))
             ->_title($this->__('Customers by Orders Total'));

        $this->_initAction()
            ->_setActiveMenu('Mage_Reports::report_customers_totals')
            ->_addBreadcrumb(Mage::helper('Mage_Reports_Helper_Data')->__('Customers by Orders Total'),
                Mage::helper('Mage_Reports_Helper_Data')->__('Customers by Orders Total'))
            ->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Report_Customer_Totals'))
            ->renderLayout();
    }

    /**
     * Export customers biggest totals report to CSV format
     */
    public function exportTotalsCsvAction()
    {
        $fileName   = 'cuatomer_totals.csv';
        $content    = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Report_Customer_Totals_Grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export customers biggest totals report to Excel XML format
     */
    public function exportTotalsExcelAction()
    {
        $fileName   = 'customer_totals.xml';
        $content    = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Report_Customer_Totals_Grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'accounts':
                return Mage::getSingleton('Mage_Backend_Model_Auth_Session')->isAllowed('Mage_Reports::accounts');
                break;
            case 'orders':
                return Mage::getSingleton('Mage_Backend_Model_Auth_Session')->isAllowed('Mage_Reports::customers_orders');
                break;
            case 'totals':
                return Mage::getSingleton('Mage_Backend_Model_Auth_Session')->isAllowed('Mage_Reports::totals');
                break;
            default:
                return Mage::getSingleton('Mage_Backend_Model_Auth_Session')->isAllowed('Mage_Reports::customers');
                break;
        }
    }
}
