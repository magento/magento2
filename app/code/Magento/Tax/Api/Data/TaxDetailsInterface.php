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
     * @api
     * @return float
     */
    public function getSubtotal();

    /**
     * Set subtotal
     *
     * @api
     * @param float $subtotal
     * @return $this
     */
    public function setSubtotal($subtotal);

    /**
     * Get tax amount
     *
     * @api
     * @return float
     */
    public function getTaxAmount();

    /**
     * Set tax amount
     *
     * @api
     * @param float $taxAmount
     * @return $this
     */
    public function setTaxAmount($taxAmount);

    /**
     * Get discount amount
     *
     * @api
     * @return float
     */
    public function getDiscountTaxCompensationAmount();

    /**
     * Set discount amount
     *
     * @api
     * @param float $discountTaxCompensationAmount
     * @return $this
     */
    public function setDiscountTaxCompensationAmount($discountTaxCompensationAmount);

    /**
     * Get applied taxes
     *
     * @api
     * @return \Magento\Tax\Api\Data\AppliedTaxInterface[] | null
     */
    public function getAppliedTaxes();

    /**
     * Set applied taxes
     *
     * @api
     * @param \Magento\Tax\Api\Data\AppliedTaxInterface[] $appliedTaxes
     * @return $this
     */
    public function setAppliedTaxes(array $appliedTaxes = null);

    /**
     * Get TaxDetails items
     *
     * @api
     * @return \Magento\Tax\Api\Data\TaxDetailsItemInterface[] | null
     */
    public function getItems();

    /**
     * Set TaxDetails items
     *
     * @api
     * @param \Magento\Tax\Api\Data\TaxDetailsItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @api
     * @return \Magento\Tax\Api\Data\TaxDetailsExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @api
     * @param \Magento\Tax\Api\Data\TaxDetailsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxDetailsExtensionInterface $extensionAttributes);
}
