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
 * @package     Mage_Paypal
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * PayPal Settlement Reports Controller
 *
 * @category    Mage
 * @package     Mage_Paypal
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Paypal_Adminhtml_Paypal_ReportsController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Grid action
     */
    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('Mage_Paypal_Block_Adminhtml_Settlement_Report'))
            ->renderLayout();
    }

    /**
     * Ajax callback for grid actions
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('Mage_Paypal_Block_Adminhtml_Settlement_Report_Grid')->toHtml()
        );
    }

    /**
     * View transaction details action
     */
    public function detailsAction()
    {
        $rowId = $this->getRequest()->getParam('id');
        $row = Mage::getModel('Mage_Paypal_Model_Report_Settlement_Row')->load($rowId);
        if (!$row->getId()) {
            $this->_redirect('*/*/');
            return;
        }
        Mage::register('current_transaction', $row);
        $this->_initAction()
            ->_title($this->__('View Transaction'))
            ->_addContent($this->getLayout()
                ->createBlock('Mage_Paypal_Block_Adminhtml_Settlement_Details', 'settlementDetails'))
            ->renderLayout();
    }

    /**
     * Forced fetch reports action
     */
    public function fetchAction()
    {
        try {
            $reports = Mage::getModel('Mage_Paypal_Model_Report_Settlement');
            /* @var $reports Mage_Paypal_Model_Report_Settlement */
            $credentials = $reports->getSftpCredentials();
            if (empty($credentials)) {
                Mage::throwException(Mage::helper('Mage_Paypal_Helper_Data')->__('Nothing to fetch because of an empty configuration.'));
            }
            foreach ($credentials as $config) {
                try {
                    $fetched = $reports->fetchAndSave($config);
                    $this->_getSession()->addSuccess(
                        Mage::helper('Mage_Paypal_Helper_Data')->__("Fetched %s report rows from '%s@%s'.", $fetched, $config['username'], $config['hostname'])
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError(
                        Mage::helper('Mage_Paypal_Helper_Data')->__("Failed to fetch reports from '%s@%s'.", $config['username'], $config['hostname'])
                    );
                    Mage::logException($e);
                }
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Initialize titles, navigation
     * @return Mage_Paypal_Adminhtml_Paypal_ReportsController
     */
    protected function _initAction()
    {
        $this->_title($this->__('Reports'))->_title($this->__('Sales'))->_title($this->__('PayPal Settlement Reports'));
        $this->loadLayout()
            ->_setActiveMenu('Mage_Paypal::report_salesroot_paypal_settlement_reports')
            ->_addBreadcrumb(Mage::helper('Mage_Paypal_Helper_Data')->__('Reports'), Mage::helper('Mage_Paypal_Helper_Data')->__('Reports'))
            ->_addBreadcrumb(Mage::helper('Mage_Paypal_Helper_Data')->__('Sales'), Mage::helper('Mage_Paypal_Helper_Data')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('Mage_Paypal_Helper_Data')->__('PayPal Settlement Reports'), Mage::helper('Mage_Paypal_Helper_Data')->__('PayPal Settlement Reports'));
        return $this;
    }

    /**
     * ACL check
     * @return bool
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'index':
            case 'details':
                return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Paypal::paypal_settlement_reports_view');
                break;
            case 'fetch':
                return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Paypal::fetch');
                break;
            default:
                return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Paypal::paypal_settlement_reports');
                break;
        }
    }
}
