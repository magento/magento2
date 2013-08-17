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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sales observer
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Observer
{
    /**
     * Expire quotes additional fields to filter
     *
     * @var array
     */
    protected $_expireQuotesFilterFields = array();

    /**
     * Clean expired quotes (cron process)
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @return Mage_Sales_Model_Observer
     */
    public function cleanExpiredQuotes($schedule)
    {
        Mage::dispatchEvent('clear_expired_quotes_before', array('sales_observer' => $this));

        $lifetimes = Mage::getConfig()->getStoresConfigByPath('checkout/cart/delete_quote_after');
        foreach ($lifetimes as $storeId=>$lifetime) {
            $lifetime *= 86400;

            /** @var $quotes Mage_Sales_Model_Resource_Quote_Collection */
            $quotes = Mage::getModel('Mage_Sales_Model_Quote')->getCollection();

            $quotes->addFieldToFilter('store_id', $storeId);
            $quotes->addFieldToFilter('updated_at', array('to'=>date("Y-m-d", time()-$lifetime)));
            $quotes->addFieldToFilter('is_active', 0);

            foreach ($this->getExpireQuotesAdditionalFilterFields() as $field => $condition) {
                $quotes->addFieldToFilter($field, $condition);
            }

            $quotes->walk('delete');
        }
        return $this;
    }

    /**
     * Retrieve expire quotes additional fields to filter
     *
     * @return array
     */
    public function getExpireQuotesAdditionalFilterFields()
    {
        return $this->_expireQuotesFilterFields;
    }

    /**
     * Set expire quotes additional fields to filter
     *
     * @param array $fields
     * @return Mage_Sales_Model_Observer
     */
    public function setExpireQuotesAdditionalFilterFields(array $fields)
    {
        $this->_expireQuotesFilterFields = $fields;
        return $this;
    }

    /**
     * Refresh sales order report statistics for last day
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @return Mage_Sales_Model_Observer
     */
    public function aggregateSalesReportOrderData($schedule)
    {
        Mage::app()->getLocale()->emulate(0);
        $currentDate = Mage::app()->getLocale()->date();
        $date = $currentDate->subHour(25);
        Mage::getResourceModel('Mage_Sales_Model_Resource_Report_Order')->aggregate($date);
        Mage::app()->getLocale()->revert();
        return $this;
    }

    /**
     * Refresh sales shipment report statistics for last day
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @return Mage_Sales_Model_Observer
     */
    public function aggregateSalesReportShipmentData($schedule)
    {
        Mage::app()->getLocale()->emulate(0);
        $currentDate = Mage::app()->getLocale()->date();
        $date = $currentDate->subHour(25);
        Mage::getResourceModel('Mage_Sales_Model_Resource_Report_Shipping')->aggregate($date);
        Mage::app()->getLocale()->revert();
        return $this;
    }

    /**
     * Refresh sales invoiced report statistics for last day
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @return Mage_Sales_Model_Observer
     */
    public function aggregateSalesReportInvoicedData($schedule)
    {
        Mage::app()->getLocale()->emulate(0);
        $currentDate = Mage::app()->getLocale()->date();
        $date = $currentDate->subHour(25);
        Mage::getResourceModel('Mage_Sales_Model_Resource_Report_Invoiced')->aggregate($date);
        Mage::app()->getLocale()->revert();
        return $this;
    }

    /**
     * Refresh sales refunded report statistics for last day
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @return Mage_Sales_Model_Observer
     */
    public function aggregateSalesReportRefundedData($schedule)
    {
        Mage::app()->getLocale()->emulate(0);
        $currentDate = Mage::app()->getLocale()->date();
        $date = $currentDate->subHour(25);
        Mage::getResourceModel('Mage_Sales_Model_Resource_Report_Refunded')->aggregate($date);
        Mage::app()->getLocale()->revert();
        return $this;
    }

    /**
     * Refresh bestsellers report statistics for last day
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @return Mage_Sales_Model_Observer
     */
    public function aggregateSalesReportBestsellersData($schedule)
    {
        Mage::app()->getLocale()->emulate(0);
        $currentDate = Mage::app()->getLocale()->date();
        $date = $currentDate->subHour(25);
        Mage::getResourceModel('Mage_Sales_Model_Resource_Report_Bestsellers')->aggregate($date);
        Mage::app()->getLocale()->revert();
        return $this;
    }

    /**
     * Set Quote information about MSRP price enabled
     *
     * @param Varien_Event_Observer $observer
     */
    public function setQuoteCanApplyMsrp(Varien_Event_Observer $observer)
    {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = $observer->getEvent()->getQuote();

        $canApplyMsrp = false;
        if (Mage::helper('Mage_Catalog_Helper_Data')->isMsrpEnabled()) {
            foreach ($quote->getAllAddresses() as $adddress) {
                if ($adddress->getCanApplyMsrp()) {
                    $canApplyMsrp = true;
                    break;
                }
            }
        }

        $quote->setCanApplyMsrp($canApplyMsrp);
    }

    /**
     * Add VAT validation request date and identifier to order comments
     *
     * @param Varien_Event_Observer $observer
     * @return null
     */
    public function addVatRequestParamsOrderComment(Varien_Event_Observer $observer)
    {
        /** @var $orderInstance Mage_Sales_Model_Order */
        $orderInstance = $observer->getOrder();
        /** @var $orderAddress Mage_Sales_Model_Order_Address */
        $orderAddress = $this->_getVatRequiredSalesAddress($orderInstance);
        if (!($orderAddress instanceof Mage_Sales_Model_Order_Address)) {
            return;
        }

        $vatRequestId = $orderAddress->getVatRequestId();
        $vatRequestDate = $orderAddress->getVatRequestDate();
        if (is_string($vatRequestId) && !empty($vatRequestId) && is_string($vatRequestDate)
            && !empty($vatRequestDate)
        ) {
            $orderHistoryComment = Mage::helper('Mage_Customer_Helper_Data')->__('VAT Request Identifier')
                . ': ' . $vatRequestId . '<br />' . Mage::helper('Mage_Customer_Helper_Data')->__('VAT Request Date')
                . ': ' . $vatRequestDate;
            $orderInstance->addStatusHistoryComment($orderHistoryComment, false);
        }
    }

    /**
     * Retrieve sales address (order or quote) on which tax calculation must be based
     *
     * @param Mage_Core_Model_Abstract $salesModel
     * @param Mage_Core_Model_Store|string|int|null $store
     * @return Mage_Customer_Model_Address_Abstract|null
     */
    protected function _getVatRequiredSalesAddress($salesModel, $store = null)
    {
        $configAddressType = Mage::helper('Mage_Customer_Helper_Address')->getTaxCalculationAddressType($store);
        $requiredAddress = null;
        switch ($configAddressType) {
            case Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING:
                $requiredAddress = $salesModel->getShippingAddress();
                break;
            default:
                $requiredAddress = $salesModel->getBillingAddress();
        }
        return $requiredAddress;
    }

    /**
     * Retrieve customer address (default billing or default shipping) ID on which tax calculation must be based
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param Mage_Core_Model_Store|string|int|null $store
     * @return int|string
     */
    protected function _getVatRequiredCustomerAddress(Mage_Customer_Model_Customer $customer, $store = null)
    {
        $configAddressType = Mage::helper('Mage_Customer_Helper_Address')->getTaxCalculationAddressType($store);
        $requiredAddress = null;
        switch ($configAddressType) {
            case Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING:
                $requiredAddress = $customer->getDefaultShipping();
                break;
            default:
                $requiredAddress = $customer->getDefaultBilling();
        }
        return $requiredAddress;
    }

    /**
     * Handle customer VAT number if needed on collect_totals_before event of quote address
     *
     * @param Varien_Event_Observer $observer
     */
    public function changeQuoteCustomerGroupId(Varien_Event_Observer $observer)
    {
        /** @var $addressHelper Mage_Customer_Helper_Address */
        $addressHelper = Mage::helper('Mage_Customer_Helper_Address');

        $quoteAddress = $observer->getQuoteAddress();
        $quoteInstance = $quoteAddress->getQuote();
        $customerInstance = $quoteInstance->getCustomer();

        $storeId = $customerInstance->getStore();

        $configAddressType = Mage::helper('Mage_Customer_Helper_Address')->getTaxCalculationAddressType($storeId);

        // When VAT is based on billing address then Magento have to handle only billing addresses
        $additionalBillingAddressCondition = ($configAddressType == Mage_Customer_Model_Address_Abstract::TYPE_BILLING)
            ? $configAddressType != $quoteAddress->getAddressType() : false;
        // Handle only addresses that corresponds to VAT configuration
        if (!$addressHelper->isVatValidationEnabled($storeId) || $additionalBillingAddressCondition) {
            return;
        }

        /** @var $customerHelper Mage_Customer_Helper_Data */
        $customerHelper = Mage::helper('Mage_Customer_Helper_Data');

        $customerCountryCode = $quoteAddress->getCountryId();
        $customerVatNumber = $quoteAddress->getVatId();

        if (empty($customerVatNumber) || !Mage::helper('Mage_Core_Helper_Data')->isCountryInEU($customerCountryCode)) {
            $groupId = ($customerInstance->getId()) ? $customerHelper->getDefaultCustomerGroupId($storeId)
                : Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;

            $quoteAddress->setPrevQuoteCustomerGroupId($quoteInstance->getCustomerGroupId());
            $customerInstance->setGroupId($groupId);
            $quoteInstance->setCustomerGroupId($groupId);

            return;
        }

        /** @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper = Mage::helper('Mage_Core_Helper_Data');
        $merchantCountryCode = $coreHelper->getMerchantCountryCode();
        $merchantVatNumber = $coreHelper->getMerchantVatNumber();

        $gatewayResponse = null;
        if ($addressHelper->getValidateOnEachTransaction($storeId)
            || $customerCountryCode != $quoteAddress->getValidatedCountryCode()
            || $customerVatNumber != $quoteAddress->getValidatedVatNumber()
        ) {
            // Send request to gateway
            $gatewayResponse = $customerHelper->checkVatNumber(
                $customerCountryCode,
                $customerVatNumber,
                ($merchantVatNumber !== '') ? $merchantCountryCode : '',
                $merchantVatNumber
            );

            // Store validation results in corresponding quote address
            $quoteAddress->setVatIsValid((int)$gatewayResponse->getIsValid())
                ->setVatRequestId($gatewayResponse->getRequestIdentifier())
                ->setVatRequestDate($gatewayResponse->getRequestDate())
                ->setVatRequestSuccess($gatewayResponse->getRequestSuccess())
                ->setValidatedVatNumber($customerVatNumber)
                ->setValidatedCountryCode($customerCountryCode)
                ->save();
        } else {
            // Restore validation results from corresponding quote address
            $gatewayResponse = new Varien_Object(array(
                'is_valid' => (int)$quoteAddress->getVatIsValid(),
                'request_identifier' => (string)$quoteAddress->getVatRequestId(),
                'request_date' => (string)$quoteAddress->getVatRequestDate(),
                'request_success' => (boolean)$quoteAddress->getVatRequestSuccess()
            ));
        }

        // Magento always has to emulate group even if customer uses default billing/shipping address
        $groupId = $customerHelper->getCustomerGroupIdBasedOnVatNumber(
            $customerCountryCode, $gatewayResponse, $customerInstance->getStore()
        );

        if ($groupId) {
            $quoteAddress->setPrevQuoteCustomerGroupId($quoteInstance->getCustomerGroupId());
            $customerInstance->setGroupId($groupId);
            $quoteInstance->setCustomerGroupId($groupId);
        }
    }

    /**
     * Restore initial customer group ID in quote if needed on collect_totals_after event of quote address
     *
     * @param Varien_Event_Observer $observer
     */
    public function restoreQuoteCustomerGroupId($observer)
    {
        $quoteAddress = $observer->getQuoteAddress();
        $configAddressType = Mage::helper('Mage_Customer_Helper_Address')->getTaxCalculationAddressType();
        // Restore initial customer group ID in quote only if VAT is calculated based on shipping address
        if ($quoteAddress->hasPrevQuoteCustomerGroupId()
            && $configAddressType == Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING
        ) {
            $quoteAddress->getQuote()->setCustomerGroupId($quoteAddress->getPrevQuoteCustomerGroupId());
            $quoteAddress->unsPrevQuoteCustomerGroupId();
        }
    }
}
