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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function convertAttribute($product, $entry)
    {
        $effectiveDateFrom = $this->getGroupAttributeSalePriceEffectiveDateFrom();
        $fromValue = $effectiveDateFrom->getProductAttributeValue($product);

        $effectiveDateTo = $this->getGroupAttributeSalePriceEffectiveDateTo();
        $toValue = $effectiveDateTo->getProductAttributeValue($product);

        $from = $to = null;
        if (!empty($fromValue)) {
            $from = new \DateTime($fromValue);
        }
        if (!empty($toValue)) {
            $to = new \DateTime($toValue);
        }

        $dateString = null;
        // if we have from an to dates, and if these dates are correct
        if ($from !== null && $to !== null && $from < $to) {
            $dateString = $from->format('Y-m-d H:i:s') . '/' . $to->format('Y-m-d H:i:s');
        }

        // if we have only "from" date, send "from" day
        if ($from !== null && $to === null) {
            $dateString = $from->format('Y-m-d');
        }

        // if we have only "to" date, use "now" date for "from"
        if ($from === null && $to !== null) {
            $from = new \DateTime();
            // if "now" date is earlier than "to" date
            if ($from < $to) {
                $dateString = $from->format('Y-m-d H:i:s') . '/' . $to->format('Y-m-d H:i:s');
            }
        }

        if ($dateString !== null) {
            $this->_setAttribute($entry, 'sale_price_effective_date', self::ATTRIBUTE_TYPE_TEXT, $dateString);
        }

        return $entry;
    }
}
