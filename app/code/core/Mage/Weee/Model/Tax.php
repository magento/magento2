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
 * @package     Mage_Weee
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Weee_Model_Tax extends Mage_Core_Model_Abstract
{
    /**
     * Including FPT only
     */
    const DISPLAY_INCL              = 0;
    /**
     * Including FPT and FPT description
     */
    const DISPLAY_INCL_DESCR        = 1;
    /**
     * Excluding FPT, FPT description, final price
     */
    const DISPLAY_EXCL_DESCR_INCL   = 2;
    /**
     * Excluding FPT
     */
    const DISPLAY_EXCL              = 3;

    protected $_allAttributes = null;
    protected $_productDiscounts = array();

    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('Mage_Weee_Model_Resource_Tax');
    }


    public function getWeeeAmount(
        $product,
        $shipping = null,
        $billing = null,
        $website = null,
        $calculateTax = false,
        $ignoreDiscount = false)
    {
        $amount = 0;
        $attributes = $this->getProductWeeeAttributes(
            $product,
            $shipping,
            $billing,
            $website,
            $calculateTax,
            $ignoreDiscount
        );
        foreach ($attributes as $attribute) {
            $amount += $attribute->getAmount();
        }
        return $amount;
    }

    public function getWeeeAttributeCodes($forceEnabled = false)
    {
        return $this->getWeeeTaxAttributeCodes($forceEnabled);
    }

    /**
     * Retrieve Wee tax attribute codes
     *
     * @param bool $forceEnabled
     * @return array
     */
    public function getWeeeTaxAttributeCodes($forceEnabled = false)
    {
        if (!$forceEnabled && !Mage::helper('Mage_Weee_Helper_Data')->isEnabled()) {
            return array();
        }

        if (is_null($this->_allAttributes)) {
            $this->_allAttributes = Mage::getModel('Mage_Eav_Model_Entity_Attribute')->getAttributeCodesByFrontendType('weee');
        }
        return $this->_allAttributes;
    }

    public function getProductWeeeAttributes(
        $product,
        $shipping = null,
        $billing = null,
        $website = null,
        $calculateTax = null,
        $ignoreDiscount = false)
    {
        $result = array();
        $allWeee = $this->getWeeeTaxAttributeCodes();
        if (!$allWeee) {
            return $result;
        }

        $websiteId = Mage::app()->getWebsite($website)->getId();
        $store = Mage::app()->getWebsite($website)->getDefaultGroup()->getDefaultStore();

        $customer = null;
        if ($shipping) {
            $customerTaxClass = $shipping->getQuote()->getCustomerTaxClassId();
            $customer = $shipping->getQuote()->getCustomer();
        } else {
            $customerTaxClass = null;
        }

        $calculator = Mage::getModel('Mage_Tax_Model_Calculation');
        if ($customer) {
            $calculator->setCustomer($customer);
        }
        $rateRequest = $calculator->getRateRequest($shipping, $billing, $customerTaxClass, $store);
        $defaultRateRequest = $calculator->getRateRequest(false, false, false, $store);

        $discountPercent = 0;
        if (!$ignoreDiscount && Mage::helper('Mage_Weee_Helper_Data')->isDiscounted($store)) {
            $discountPercent = $this->_getDiscountPercentForProduct($product);
        }

        $productAttributes = $product->getTypeInstance()->getSetAttributes($product);
        foreach ($productAttributes as $code => $attribute) {
            if (in_array($code, $allWeee)) {

                $attributeSelect = $this->getResource()->getReadConnection()->select();
                $attributeSelect
                    ->from($this->getResource()->getTable('weee_tax'), 'value')
                    ->where('attribute_id = ?', (int)$attribute->getId())
                    ->where('website_id IN(?)', array($websiteId, 0))
                    ->where('country = ?', $rateRequest->getCountryId())
                    ->where('state IN(?)', array($rateRequest->getRegionId(), '*'))
                    ->where('entity_id = ?', (int)$product->getId())
                    ->limit(1);

                $order = array('state ' . Varien_Db_Select::SQL_DESC, 'website_id ' . Varien_Db_Select::SQL_DESC);
                $attributeSelect->order($order);

                $value = $this->getResource()->getReadConnection()->fetchOne($attributeSelect);
                if ($value) {
                    if ($discountPercent) {
                        $value = Mage::app()->getStore()->roundPrice($value-($value*$discountPercent/100));
                    }

                    $taxAmount = $amount = 0;
                    $amount    = $value;
                    /**
                     * We can't use FPT imcluding/excluding tax
                     */
//                    if ($calculateTax && Mage::helper('Mage_Weee_Helper_Data')->isTaxable($store)) {
//                        $defaultPercent = Mage::getModel('Mage_Tax_Model_Calculation')
//                              ->getRate($defaultRateRequest
//                              ->setProductClassId($product->getTaxClassId()));
//                        $currentPercent = $product->getTaxPercent();
//
//                        $taxAmount = Mage::app()->getStore()->roundPrice($value/(100+$defaultPercent)*$currentPercent);
//                        $amount = $value - $taxAmount;
//                    }

                    $one = new Varien_Object();
                    $one->setName(Mage::helper('Mage_Catalog_Helper_Data')->__($attribute->getFrontend()->getLabel()))
                        ->setAmount($amount)
                        ->setTaxAmount($taxAmount)
                        ->setCode($attribute->getAttributeCode());

                    $result[] = $one;
                }
            }
        }
        return $result;
    }

    protected function _getDiscountPercentForProduct($product)
    {
        $website = Mage::app()->getStore()->getWebsiteId();
        $group = Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerGroupId();
        $key = implode('-', array($website, $group, $product->getId()));
        if (!isset($this->_productDiscounts[$key])) {
            $this->_productDiscounts[$key] = (int) $this->getResource()
                ->getProductDiscountPercent($product->getId(), $website, $group);
        }
        if ($value = $this->_productDiscounts[$key]) {
            return 100-min(100, max(0, $value));
        } else {
            return 0;
        }
    }

    /**
     * Update discounts for FPT amounts of all products
     *
     * @return Mage_Weee_Model_Tax
     */
    public function updateDiscountPercents()
    {
        $this->getResource()->updateDiscountPercents();
        return $this;
    }

    /**
     * Update discounts for FPT amounts base on products condiotion
     *
     * @param  mixed $products
     * @return Mage_Weee_Model_Tax
     */
    public function updateProductsDiscountPercent($products)
    {
        $this->getResource()->updateProductsDiscountPercent($products);
        return $this;
    }
}
