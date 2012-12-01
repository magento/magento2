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
 * @package     Mage_GoogleShopping
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tax attribute model
 *
 * @category   Mage
 * @package    Mage_GoogleShopping
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_GoogleShopping_Model_Attribute_Tax extends Mage_GoogleShopping_Model_Attribute_Default
{
    /**
     * Maximum number of tax rates per product supported by google shopping api
     */
    const RATES_MAX = 100;
    /**
     * Set current attribute to entry (for specified product)
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Varien_Gdata_Gshopping_Entry $entry
     * @return Varien_Gdata_Gshopping_Entry
     */
    public function convertAttribute($product, $entry)
    {
        $entry->cleanTaxes();
        if (Mage::helper('Mage_Tax_Helper_Data')->getConfig()->priceIncludesTax()) {
            return $entry;
        }

        $calc = Mage::helper('Mage_Tax_Helper_Data')->getCalculator();
        $customerTaxClass = $calc->getDefaultCustomerTaxClass($product->getStoreId());
        $rates = $calc->getRatesByCustomerAndProductTaxClasses($customerTaxClass, $product->getTaxClassId());
        $targetCountry = Mage::getSingleton('Mage_GoogleShopping_Model_Config')->getTargetCountry($product->getStoreId());
        $ratesTotal = 0;
        foreach ($rates as $rate) {
            if ($targetCountry == $rate['country']) {
                $regions = $this->_parseRegions($rate['state'], $rate['postcode']);
                $ratesTotal += count($regions);
                if ($ratesTotal > self::RATES_MAX) {
                    Mage::throwException(Mage::helper('Mage_GoogleShopping_Helper_Data')->__("Google shopping only supports %d tax rates per product", self::RATES_MAX));
                }
                foreach ($regions as $region) {
                    $entry->addTax(array(
                        'tax_rate' =>     $rate['value'] * 100,
                        'tax_country' =>  empty($rate['country']) ? '*' : $rate['country'],
                        'tax_region' =>   $region
                    ));
                }
            }
        }

        return $entry;
    }

    /**
     * Retrieve array of regions characterized by provided params
     *
     * @param string $state
     * @param string $zip
     * @return array
     */
    protected function _parseRegions($state, $zip)
    {
        return (!empty($zip) && $zip != '*') ? $this->_parseZip($zip) : (($state) ? array($state) : array('*'));
    }

    /**
     * Retrieve array of regions characterized by provided zip code
     *
     * @param string $zip
     * @return array
     */
    protected function _parseZip($zip)
    {
        if (strpos($zip, '-') == -1) {
            return array($zip);
        } else {
            return Mage::helper('Mage_GoogleCheckout_Helper_Data')->zipRangeToZipPattern($zip);
        }
    }
}
