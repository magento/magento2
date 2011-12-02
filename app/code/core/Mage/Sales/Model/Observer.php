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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
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
     * When deleting product, substract it from all quotes quantities
     *
     * @throws Exception
     * @param Varien_Event_Observer
     * @return Mage_Sales_Model_Observer
     */
    public function substractQtyFromQuotes($observer)
    {
        $product = $observer->getEvent()->getProduct();
        Mage::getResourceSingleton('Mage_Sales_Model_Resource_Quote')->substractProductFromQuotes($product);
        return $this;
    }

    /**
     * When applying a catalog price rule, make related quotes recollect on demand
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Sales_Model_Observer
     */
    public function markQuotesRecollectOnCatalogRules($observer)
    {
        Mage::getResourceSingleton('Mage_Sales_Model_Resource_Quote')->markQuotesRecollectOnCatalogRules();
        return $this;
    }

    /**
     * Catalog Product After Save (change status process)
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Sales_Model_Observer
     */
    public function catalogProductSaveAfter(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
            return $this;
        }

        Mage::getResourceSingleton('Mage_Sales_Model_Resource_Quote')->markQuotesRecollect($product->getId());

        return $this;
    }

    /**
     * Catalog Mass Status update process
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Sales_Model_Observer
     */
    public function catalogProductStatusUpdate(Varien_Event_Observer $observer)
    {
        $status     = $observer->getEvent()->getStatus();
        if ($status == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
            return $this;
        }
        $productId  = $observer->getEvent()->getProductId();
        Mage::getResourceSingleton('Mage_Sales_Model_Resource_Quote')->markQuotesRecollect($productId);

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
     * Add the recurring profile form when editing a product
     *
     * @param Varien_Event_Observer $observer
     */
    public function prepareProductEditFormRecurringProfile($observer)
    {
        // replace the element of recurring payment profile field with a form
        $profileElement = $observer->getEvent()->getProductElement();
        $block = Mage::app()->getLayout()->createBlock('Mage_Sales_Block_Adminhtml_Recurring_Profile_Edit_Form',
            'adminhtml_recurring_profile_edit_form')->setParentElement($profileElement)
            ->setProductEntity($observer->getEvent()->getProduct());
        $observer->getEvent()->getResult()->output = $block->toHtml();

        // make the profile element dependent on is_recurring
        $dependencies = Mage::app()->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Form_Element_Dependence',
            'adminhtml_recurring_profile_edit_form_dependence')->addFieldMap('is_recurring', 'product[is_recurring]')
            ->addFieldMap($profileElement->getHtmlId(), $profileElement->getName())
            ->addFieldDependence($profileElement->getName(), 'product[is_recurring]', '1')
            ->addConfigOptions(array('levels_up' => 2));
        $observer->getEvent()->getResult()->output .= $dependencies->toHtml();
    }

    /**
     * Block admin ability to use customer billing agreements
     *
     * @param Varien_Event_Observer $observer
     */
    public function restrictAdminBillingAgreementUsage($observer)
    {
        $methodInstance = $observer->getEvent()->getMethodInstance();
        if (!($methodInstance instanceof Mage_Sales_Model_Payment_Method_Billing_AgreementAbstract)) {
            return;
        }
        if (!Mage::getSingleton('Mage_Admin_Model_Session')->isAllowed('sales/order/actions/use')) {
            $observer->getEvent()->getResult()->isAvailable = false;
        }
    }

    /**
     * Set new customer group to all his quotes
     *
     * @param  Varien_Event_Observer $observer
     * @return Mage_Sales_Model_Observer
     */
    public function customerSaveAfter(Varien_Event_Observer $observer)
    {
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = $observer->getEvent()->getCustomer();

        if ($customer->getGroupId() !== $customer->getOrigData('group_id')) {
            /**
             * It is needed to process customer's quotes for all websites
             * if customer accounts are shared between all of them
             */
            $websites = (Mage::getSingleton('Mage_Customer_Model_Config_Share')->isWebsiteScope())
                ? array(Mage::app()->getWebsite($customer->getWebsiteId()))
                : Mage::app()->getWebsites();

            /** @var $quote Mage_Sales_Model_Quote */
            $quote = Mage::getSingleton('Mage_Sales_Model_Quote');

            foreach ($websites as $website) {
                $quote->setWebsite($website);
                $quote->loadByCustomer($customer);

                if ($quote->getId()) {
                    $quote->setCustomerGroupId($customer->getGroupId());
                    $quote->collectTotals();
                    $quote->save();
                }
            }
        }

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
}

