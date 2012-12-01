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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * WEEE data helper
 *
 * @category Mage
 * @package  Mage_Weee
 * @author   Magento Core Team <core@magentocommerce.com>
 */
class Mage_Weee_Helper_Data extends Mage_Core_Helper_Abstract
{

    const XML_PATH_FPT_ENABLED       = 'tax/weee/enable';

    protected $_storeDisplayConfig   = array();

    /**
     * Get weee amount display type on product view page
     *
     * @param   mixed $store
     * @return  int
     */
    public function getPriceDisplayType($store = null)
    {
        return Mage::getStoreConfig('tax/weee/display', $store);
    }

    /**
     * Get weee amount display type on product list page
     *
     * @param   mixed $store
     * @return  int
     */
    public function getListPriceDisplayType($store = null)
    {
        return Mage::getStoreConfig('tax/weee/display_list', $store);
    }

    /**
     * Get weee amount display type in sales modules
     *
     * @param   mixed $store
     * @return  int
     */
    public function getSalesPriceDisplayType($store = null)
    {
        return Mage::getStoreConfig('tax/weee/display_sales', $store);
    }

    /**
     * Get weee amount display type in email templates
     *
     * @param   mixed $store
     * @return  int
     */
    public function getEmailPriceDisplayType($store = null)
    {
        return Mage::getStoreConfig('tax/weee/display_email', $store);
    }

    /**
     * Check if weee tax amount should be discounted
     *
     * @param   mixed $store
     * @return  bool
     */
    public function isDiscounted($store = null)
    {
        return Mage::getStoreConfigFlag('tax/weee/discount', $store);
    }

    /**
     * Check if weee tax amount should be taxable
     *
     * @param   mixed $store
     * @return  bool
     */
    public function isTaxable($store = null)
    {
        return Mage::getStoreConfigFlag('tax/weee/apply_vat', $store);
    }

    /**
     * Check if weee tax amount should be included to subtotal
     *
     * @param   mixed $store
     * @return  bool
     */
    public function includeInSubtotal($store = null)
    {
        return Mage::getStoreConfigFlag('tax/weee/include_in_subtotal', $store);
    }

    /**
     * Get weee tax amount for product based on shipping and billing addresses, website and tax settings
     *
     * @param   Mage_Catalog_Model_Product $product
     * @param   null|Mage_Customer_Model_Address_Abstract $shipping
     * @param   null|Mage_Customer_Model_Address_Abstract $billing
     * @param   mixed $website
     * @param   bool $calculateTaxes
     * @return  float
     */
    public function getAmount($product, $shipping = null, $billing = null, $website = null, $calculateTaxes = false)
    {
        if ($this->isEnabled()) {
            return Mage::getSingleton('Mage_Weee_Model_Tax')->
                    getWeeeAmount($product, $shipping, $billing, $website, $calculateTaxes);
        }
        return 0;
    }

    /**
     * Returns diaplay type for price accordingly to current zone
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array|null                 $compareTo
     * @param string                     $zone
     * @param Mage_Core_Model_Store      $store
     * @return bool|int
     */
    public function typeOfDisplay($product, $compareTo = null, $zone = null, $store = null)
    {
        if (!$this->isEnabled($store)) {
            return false;
        }
        switch ($zone) {
            case 'product_view':
                $type = $this->getPriceDisplayType($store);
                break;
            case 'product_list':
                $type = $this->getListPriceDisplayType($store);
                break;
            case 'sales':
                $type = $this->getSalesPriceDisplayType($store);
                break;
            case 'email':
                $type = $this->getEmailPriceDisplayType($store);
                break;
            default:
                if (Mage::registry('current_product')) {
                    $type = $this->getPriceDisplayType($store);
                } else {
                    $type = $this->getListPriceDisplayType($store);
                }
                break;
        }

        if (is_null($compareTo)) {
            return $type;
        } else {
            if (is_array($compareTo)) {
                return in_array($type, $compareTo);
            } else {
                return $type == $compareTo;
            }
        }
    }

    /**
     * Proxy for Mage_Weee_Model_Tax::getProductWeeeAttributes()
     *
     * @param Mage_Catalog_Model_Product $product
     * @param null|false|Varien_Object   $shipping
     * @param null|false|Varien_Object   $billing
     * @param Mage_Core_Model_Website    $website
     * @param bool                       $calculateTaxes
     * @return array
     */
    public function getProductWeeeAttributes($product, $shipping = null, $billing = null,
        $website = null, $calculateTaxes = false)
    {
        return Mage::getSingleton('Mage_Weee_Model_Tax')
                ->getProductWeeeAttributes($product, $shipping, $billing, $website, $calculateTaxes);
    }

    /**
     * Returns applied weee taxes
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @return array
     */
    public function getApplied($item)
    {
        if ($item instanceof Mage_Sales_Model_Quote_Item_Abstract) {
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                $result = array();
                foreach ($item->getChildren() as $child) {
                    $childData = $this->getApplied($child);
                    if (is_array($childData)) {
                        $result = array_merge($result, $childData);
                    }
                }
                return $result;
            }
        }

        /**
         * if order item data is old enough then weee_tax_applied cab be
         * not valid serialized data
         */
        $data = $item->getWeeeTaxApplied();
        if (empty($data)){
            return array();
        }
        return unserialize($item->getWeeeTaxApplied());
    }

    /**
     * Sets applied weee taxes
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @param array                                $value
     * @return Mage_Weee_Helper_Data
     */
    public function setApplied($item, $value)
    {
        $item->setWeeeTaxApplied(serialize($value));
        return $this;
    }

    /**
     * Returns array of weee attributes allowed for display
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getProductWeeeAttributesForDisplay($product)
    {
        if ($this->isEnabled()) {
            return $this->getProductWeeeAttributes($product, null, null, null, $this->typeOfDisplay($product, 1));
        }
        return array();
    }

    /**
     * Get Product Weee attributes for price renderer
     *
     * @param Mage_Catalog_Model_Product $product
     * @param null|false|Varien_Object $shipping Shipping Address
     * @param null|false|Varien_Object $billing Billing Address
     * @param null|Mage_Core_Model_Website $website
     * @param mixed $calculateTaxes
     * @return array
     */
    public function getProductWeeeAttributesForRenderer($product, $shipping = null, $billing = null,
        $website = null, $calculateTaxes = false)
    {
        if ($this->isEnabled()) {
            return $this->getProductWeeeAttributes(
                $product,
                $shipping,
                $billing,
                $website,
                $calculateTaxes ? $calculateTaxes : $this->typeOfDisplay($product, 1)
            );
        }
        return array();
    }

    /**
     * Returns amount to display
     *
     * @param Mage_Catalog_Model_Product $product
     * @return int
     */
    public function getAmountForDisplay($product)
    {
        if ($this->isEnabled()) {
            return Mage::getModel('Mage_Weee_Model_Tax')
                    ->getWeeeAmount($product, null, null, null, $this->typeOfDisplay($product, 1));
        }
        return 0;
    }

    /**
     * Returns original amount
     *
     * @param Mage_Catalog_Model_Product $product
     * @return int
     */
    public function getOriginalAmount($product)
    {
        if ($this->isEnabled()) {
            return Mage::getModel('Mage_Weee_Model_Tax')->getWeeeAmount($product, null, null, null, false, true);
        }
        return 0;
    }

    /**
     * Adds HTML containers and formats tier prices accordingly to the currency used
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array                      $tierPrices
     * @return Mage_Weee_Helper_Data
     */
    public function processTierPrices($product, &$tierPrices)
    {
        $weeeAmount = $this->getAmountForDisplay($product);
        $store = Mage::app()->getStore();
        foreach ($tierPrices as $index => &$tier) {
            $html = $store->formatPrice($store->convertPrice(
                Mage::helper('Mage_Tax_Helper_Data')->getPrice($product, $tier['website_price'], true)+$weeeAmount), false);
            $tier['formated_price_incl_weee'] = '<span class="price tier-' . $index . '-incl-tax">' . $html . '</span>';
            $html = $store->formatPrice($store->convertPrice(
                Mage::helper('Mage_Tax_Helper_Data')->getPrice($product, $tier['website_price'])+$weeeAmount), false);
            $tier['formated_price_incl_weee_only'] = '<span class="price tier-' . $index . '">' . $html . '</span>';
            $tier['formated_weee'] = $store->formatPrice($store->convertPrice($weeeAmount));
        }
        return $this;
    }

    /**
     * Check if fixed taxes are used in system
     *
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function isEnabled($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_FPT_ENABLED, $store);
    }

    /**
     * Returns all summed WEEE taxes with all local taxes applied
     *
     * @throws Mage_Exception
     * @param array $attributes Array of Varien_Object, result from getProductWeeeAttributes()
     * @return float
     */
    public function getAmountInclTaxes($attributes)
    {
        if (is_array($attributes)) {
            $amount = 0;
            foreach ($attributes as $attribute) {
                /* @var $attribute Varien_Object */
                $amount += $attribute->getAmount() + $attribute->getTaxAmount();
            }
        } else {
            throw new Mage_Exception('$attributes must be an array');
        }

        return (float)$amount;
    }
}
