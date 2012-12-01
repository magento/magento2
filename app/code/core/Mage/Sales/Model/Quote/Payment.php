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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Quote payment information
 *
 * @method Mage_Sales_Model_Resource_Quote_Payment _getResource()
 * @method Mage_Sales_Model_Resource_Quote_Payment getResource()
 * @method int getQuoteId()
 * @method Mage_Sales_Model_Quote_Payment setQuoteId(int $value)
 * @method string getCreatedAt()
 * @method Mage_Sales_Model_Quote_Payment setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method Mage_Sales_Model_Quote_Payment setUpdatedAt(string $value)
 * @method string getMethod()
 * @method Mage_Sales_Model_Quote_Payment setMethod(string $value)
 * @method string getCcType()
 * @method Mage_Sales_Model_Quote_Payment setCcType(string $value)
 * @method string getCcNumberEnc()
 * @method Mage_Sales_Model_Quote_Payment setCcNumberEnc(string $value)
 * @method string getCcLast4()
 * @method Mage_Sales_Model_Quote_Payment setCcLast4(string $value)
 * @method string getCcCidEnc()
 * @method Mage_Sales_Model_Quote_Payment setCcCidEnc(string $value)
 * @method string getCcSsOwner()
 * @method Mage_Sales_Model_Quote_Payment setCcSsOwner(string $value)
 * @method int getCcSsStartMonth()
 * @method Mage_Sales_Model_Quote_Payment setCcSsStartMonth(int $value)
 * @method int getCcSsStartYear()
 * @method Mage_Sales_Model_Quote_Payment setCcSsStartYear(int $value)
 * @method string getPaypalCorrelationId()
 * @method Mage_Sales_Model_Quote_Payment setPaypalCorrelationId(string $value)
 * @method string getPaypalPayerId()
 * @method Mage_Sales_Model_Quote_Payment setPaypalPayerId(string $value)
 * @method string getPaypalPayerStatus()
 * @method Mage_Sales_Model_Quote_Payment setPaypalPayerStatus(string $value)
 * @method string getPoNumber()
 * @method Mage_Sales_Model_Quote_Payment setPoNumber(string $value)
 * @method string getAdditionalData()
 * @method Mage_Sales_Model_Quote_Payment setAdditionalData(string $value)
 * @method string getCcSsIssue()
 * @method Mage_Sales_Model_Quote_Payment setCcSsIssue(string $value)
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Quote_Payment extends Mage_Payment_Model_Info
{
    protected $_eventPrefix = 'sales_quote_payment';
    protected $_eventObject = 'payment';

    protected $_quote;

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('Mage_Sales_Model_Resource_Quote_Payment');
    }

    /**
     * Declare quote model instance
     *
     * @param   Mage_Sales_Model_Quote $quote
     * @return  Mage_Sales_Model_Quote_Payment
     */
    public function setQuote(Mage_Sales_Model_Quote $quote)
    {
        $this->_quote = $quote;
        $this->setQuoteId($quote->getId());
        return $this;
    }

    /**
     * Retrieve quote model instance
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->_quote;
    }

    /**
     * Import data array to payment method object,
     * Method calls quote totals collect because payment method availability
     * can be related to quote totals
     *
     * @param   array $data
     * @throws  Mage_Core_Exception
     * @return  Mage_Sales_Model_Quote_Payment
     */
    public function importData(array $data)
    {
        $data = new Varien_Object($data);
        Mage::dispatchEvent(
            $this->_eventPrefix . '_import_data_before',
            array(
                $this->_eventObject=>$this,
                'input'=>$data,
            )
        );

        $this->setMethod($data->getMethod());
        $method = $this->getMethodInstance();

        /**
         * Payment availability related with quote totals.
         * We have to recollect quote totals before checking
         */
        $this->getQuote()->collectTotals();

        if (!$method->isAvailable($this->getQuote())
            || !$method->isApplicableToQuote($this->getQuote(), $data->getChecks())
        ) {
            Mage::throwException(Mage::helper('Mage_Sales_Helper_Data')->__('The requested Payment Method is not available.'));
        }

        $method->assignData($data);
        /*
        * validating the payment data
        */
        $method->validate();
        return $this;
    }

    /**
     * Prepare object for save
     *
     * @return Mage_Sales_Model_Quote_Payment
     */
    protected function _beforeSave()
    {
        if ($this->getQuote()) {
            $this->setQuoteId($this->getQuote()->getId());
        }
        try {
            $method = $this->getMethodInstance();
        } catch (Mage_Core_Exception $e) {
            return parent::_beforeSave();
        }
        $method->prepareSave();
        return parent::_beforeSave();
    }

    /**
     * Checkout redirect URL getter
     *
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        $method = $this->getMethodInstance();
        if ($method) {
            return $method->getCheckoutRedirectUrl();
        }
        return '';
    }

    /**
     * Checkout order place redirect URL getter
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        $method = $this->getMethodInstance();
        if ($method) {
            return $method->getOrderPlaceRedirectUrl();
        }
        return '';
    }

    /**
     * Retrieve payment method model object
     *
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function getMethodInstance()
    {
        $method = parent::getMethodInstance();
        return $method->setStore($this->getQuote()->getStore());
    }
}
