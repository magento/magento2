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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sale price effective date attribute model.
 *
 * @category   Mage
 * @package    Mage_GoogleShopping
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_GoogleShopping_Model_Attribute_SalePriceEffectiveDate extends Mage_GoogleShopping_Model_Attribute_Default
{
    /**
     * Set current attribute to entry (for specified product)
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Varien_Gdata_Gshopping_Entry $entry
     * @return Varien_Gdata_Gshopping_Entry
     */
    public function convertAttribute($product, $entry)
    {
        $effectiveDateFrom = $this->getGroupAttributeSalePriceEffectiveDateFrom();
        $fromValue = $effectiveDateFrom->getProductAttributeValue($product);

        $effectiveDateTo = $this->getGroupAttributeSalePriceEffectiveDateTo();
        $toValue = $effectiveDateTo->getProductAttributeValue($product);

        $from = $to = null;
        if (!empty($fromValue) && Zend_Date::isDate($fromValue, Zend_Date::ATOM)) {
            $from = new Zend_Date($fromValue, Zend_Date::ATOM);
        }
        if (!empty($toValue) && Zend_Date::isDate($toValue, Zend_Date::ATOM)) {
            $to = new Zend_Date($toValue, Zend_Date::ATOM);
        }

        $dateString = null;
        // if we have from an to dates, and if these dates are correct
        if (!is_null($from) && !is_null($to) && $from->isEarlier($to)) {
            $dateString = $from->toString(Zend_Date::ATOM) . '/' . $to->toString(Zend_Date::ATOM);
        }

        // if we have only "from" date, send "from" day
        if (!is_null($from) && is_null($to)) {
            $dateString = $from->toString('YYYY-MM-dd');
        }

        // if we have only "to" date, use "now" date for "from"
        if (is_null($from) && !is_null($to)) {
            $from = new Zend_Date();
            // if "now" date is earlier than "to" date
            if ($from->isEarlier($to)) {
                $dateString = $from->toString(Zend_Date::ATOM) . '/' . $to->toString(Zend_Date::ATOM);
            }
        }

        if (!is_null($dateString)) {
            $this->_setAttribute($entry, 'sale_price_effective_date', self::ATTRIBUTE_TYPE_TEXT, $dateString);
        }

        return $entry;
    }
}
