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
 * @package     Mage_SalesRule
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * SalesRule data helper
 */
class Mage_SalesRule_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Set store and base price which will be used during discount calculation to item object
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @param   float $basePrice
     * @param   float $price
     * @return  Mage_SalesRule_Helper_Data
     */
    public function setItemDiscountPrices(Mage_Sales_Model_Quote_Item_Abstract $item, $basePrice, $price)
    {
        $item->setDiscountCalculationPrice($price);
        $item->setBaseDiscountCalculationPrice($basePrice);
        return $this;
    }

    /**
     * Add additional amounts to discount calculation prices
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @param   float $basePrice
     * @param   float $price
     * @return  Mage_SalesRule_Helper_Data
     */
    public function addItemDiscountPrices(Mage_Sales_Model_Quote_Item_Abstract $item, $basePrice, $price)
    {
        $discountPrice      = $item->getDiscountCalculationPrice();
        $baseDiscountPrice  = $item->getBaseDiscountCalculationPrice();

        if ($discountPrice || $baseDiscountPrice || $basePrice || $price) {
            $discountPrice      = $discountPrice ? $discountPrice : $item->getCalculationPrice();
            $baseDiscountPrice  = $baseDiscountPrice ? $baseDiscountPrice : $item->getBaseCalculationPrice();
            $this->setItemDiscountPrices($item, $baseDiscountPrice+$basePrice, $discountPrice+$price);
        }
        return $this;
    }
}
