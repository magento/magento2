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
 * @package     Mage_CurrencySymbol
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml Currency Symbols Controller
 *
 * @category    Mage
 * @package     currencysymbol
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_CurrencySymbol_Adminhtml_System_CurrencysymbolController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Show Currency Symbols Management dialog
     */
    public function indexAction()
    {
        // set active menu and breadcrumbs
        $this->loadLayout()
            ->_setActiveMenu('Mage_Adminhtml::system_currency')
            ->_addBreadcrumb(
                Mage::helper('Mage_CurrencySymbol_Helper_Data')->__('System'),
                Mage::helper('Mage_CurrencySymbol_Helper_Data')->__('System')
            )
            ->_addBreadcrumb(
                Mage::helper('Mage_CurrencySymbol_Helper_Data')->__('Manage Currency Rates'),
                Mage::helper('Mage_CurrencySymbol_Helper_Data')->__('Manage Currency Rates')
            );

        $this->_title($this->__('System'))
            ->_title($this->__('Manage Currency Symbols'));
        $this->renderLayout();
    }

    /**
     * Save custom Currency symbol
     */
    public function saveAction()
    {
        $symbolsDataArray = $this->getRequest()->getParam('custom_currency_symbol', null);
        if (is_array($symbolsDataArray)) {
            foreach ($symbolsDataArray as &$symbolsData) {
                $symbolsData = Mage::helper('Mage_Adminhtml_Helper_Data')->stripTags($symbolsData);
            }
        }

        try {
            Mage::getModel('Mage_CurrencySymbol_Model_System_Currencysymbol')->setCurrencySymbolsData($symbolsDataArray);
            Mage::getSingleton('Mage_Connect_Model_Session')->addSuccess(
                Mage::helper('Mage_CurrencySymbol_Helper_Data')->__('Custom currency symbols were applied successfully.')
            );
        } catch (Exception $e) {
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
        }

        $this->_redirectReferer();
    }

    /**
     * Resets custom Currency symbol for all store views, websites and default value
     */
    public function resetAction()
    {
        Mage::getModel('Mage_CurrencySymbol_Model_System_Currencysymbol')->resetValues();
        $this->_redirectReferer();
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_CurrencySymbol::symbols');
    }
}
