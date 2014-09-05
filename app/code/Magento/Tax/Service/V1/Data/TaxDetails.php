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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Tax\Service\V1\Data;

class TaxDetails extends \Magento\Framework\Service\Data\AbstractExtensibleObject
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_SUBTOTAL = 'subtotal';

    const KEY_TAX_AMOUNT = 'tax_amount';

    const KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT = 'discount_tax_compensation_amount';

    const KEY_APPLIED_TAXES = 'applied_taxes';

    const KEY_ITEMS = 'items';

    /**#@-*/

    /**
     * Get subtotal
     *
     * @return float
     */
    public function getSubtotal()
    {
        return $this->_get(self::KEY_SUBTOTAL);
    }

    /**
     * Get tax amount
     *
     * @return float
     */
    public function getTaxAmount()
    {
        return $this->_get(self::KEY_TAX_AMOUNT);
    }

    /**
     * Get discount amount
     *
     * @return float
     */
    public function getDiscountTaxCompensationAmount()
    {
        return $this->_get(self::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT);
    }

    /**
     * Get applied taxes
     *
     * @return \Magento\Tax\Service\V1\Data\TaxDetails\AppliedTax[] | null
     */
    public function getAppliedTaxes()
    {
        return $this->_get(self::KEY_APPLIED_TAXES);
    }

    /**
     * Get TaxDetails items
     *
     * @return \Magento\Tax\Service\V1\Data\TaxDetails\Item[] | null
     */
    public function getItems()
    {
        return $this->_get(self::KEY_ITEMS);
    }
}
