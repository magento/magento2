<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

interface TaxDetailsInterface extends \Magento\Framework\Api\ExtensibleDataInterface
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
    public function getSubtotal();

    /**
     * Get tax amount
     *
     * @return float
     */
    public function getTaxAmount();

    /**
     * Get discount amount
     *
     * @return float
     */
    public function getDiscountTaxCompensationAmount();

    /**
     * Get applied taxes
     *
     * @return \Magento\Tax\Api\Data\AppliedTaxInterface[] | null
     */
    public function getAppliedTaxes();

    /**
     * Get TaxDetails items
     *
     * @return \Magento\Tax\Api\Data\TaxDetailsItemInterface[] | null
     */
    public function getItems();
}
