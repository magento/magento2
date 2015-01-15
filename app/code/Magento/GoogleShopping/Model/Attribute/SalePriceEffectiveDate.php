<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sale price effective date attribute model.
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\GoogleShopping\Model\Attribute;

class SalePriceEffectiveDate extends \Magento\GoogleShopping\Model\Attribute\DefaultAttribute
{
    /**
     * Set current attribute to entry (for specified product)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Gdata\Gshopping\Entry $entry
     * @return \Magento\Framework\Gdata\Gshopping\Entry
     */
    public function convertAttribute($product, $entry)
    {
        $effectiveDateFrom = $this->getGroupAttributeSalePriceEffectiveDateFrom();
        $fromValue = $effectiveDateFrom->getProductAttributeValue($product);

        $effectiveDateTo = $this->getGroupAttributeSalePriceEffectiveDateTo();
        $toValue = $effectiveDateTo->getProductAttributeValue($product);

        $from = $to = null;
        if (!empty($fromValue) && \Zend_Date::isDate($fromValue, \Zend_Date::ATOM)) {
            $from = new \Magento\Framework\Stdlib\DateTime\Date($fromValue, \Zend_Date::ATOM);
        }
        if (!empty($toValue) && \Zend_Date::isDate($toValue, \Zend_Date::ATOM)) {
            $to = new \Magento\Framework\Stdlib\DateTime\Date($toValue, \Zend_Date::ATOM);
        }

        $dateString = null;
        // if we have from an to dates, and if these dates are correct
        if (!is_null($from) && !is_null($to) && $from->isEarlier($to)) {
            $dateString = $from->toString(\Zend_Date::ATOM) . '/' . $to->toString(\Zend_Date::ATOM);
        }

        // if we have only "from" date, send "from" day
        if (!is_null($from) && is_null($to)) {
            $dateString = $from->toString('YYYY-MM-dd');
        }

        // if we have only "to" date, use "now" date for "from"
        if (is_null($from) && !is_null($to)) {
            $from = new \Magento\Framework\Stdlib\DateTime\Date();
            // if "now" date is earlier than "to" date
            if ($from->isEarlier($to)) {
                $dateString = $from->toString(\Zend_Date::ATOM) . '/' . $to->toString(\Zend_Date::ATOM);
            }
        }

        if (!is_null($dateString)) {
            $this->_setAttribute($entry, 'sale_price_effective_date', self::ATTRIBUTE_TYPE_TEXT, $dateString);
        }

        return $entry;
    }
}
