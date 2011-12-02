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
 * @package     Mage_Bundle
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Product View block (to modify getTierPrices method)
 *
 * @category   Mage
 * @package    Mage_Bundle
 * @module     Catalog
 */
class Mage_Bundle_Block_Catalog_Product_View extends Mage_Catalog_Block_Product_View
{
    /**
     * Get tier prices (formatted)
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getTierPrices($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        $prices  = $product->getFormatedTierPrice();

        $taxHelper = Mage::helper('Mage_Tax_Helper_Data');

        $res = array();
        if (is_array($prices)) {
            foreach ($prices as $price) {
                $price['price_qty'] = $price['price_qty']*1;
                $price['savePercent'] = ceil(100 - $price['price']);
                $price['formated_price'] = Mage::app()->getStore()->formatPrice(
                    Mage::app()->getStore()->convertPrice($taxHelper->getPrice($product, $price['website_price']))
                );
                $price['formated_price_incl_tax'] = Mage::app()->getStore()->formatPrice(
                    Mage::app()->getStore()->convertPrice($taxHelper->getPrice($product, $price['website_price'], true))
                );
                $res[] = $price;
            }
        }

        return $res;
    }
}
