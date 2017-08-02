<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

/**
 * Tax details interface.
 * @api
 * @since 2.0.0
 */
interface TaxDetailsInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get subtotal
     *
     * @return float
     * @since 2.0.0
     */
    public function getSubtotal();

    /**
     * Set subtotal
     *
     * @param float $subtotal
     * @return $this
     * @since 2.0.0
     */
    public function setSubtotal($subtotal);

    /**
     * Get tax amount
     *
     * @return float
     * @since 2.0.0
     */
    public function getTaxAmount();

    /**
     * Set tax amount
     *
     * @param float $taxAmount
     * @return $this
     * @since 2.0.0
     */
    public function setTaxAmount($taxAmount);

    /**
     * Get discount amount
     *
     * @return float
     * @since 2.0.0
     */
    public function getDiscountTaxCompensationAmount();

    /**
     * Set discount amount
     *
     * @param float $discountTaxCompensationAmount
     * @return $this
     * @since 2.0.0
     */
    public function setDiscountTaxCompensationAmount($discountTaxCompensationAmount);

    /**
     * Get applied taxes
     *
     * @return \Magento\Tax\Api\Data\AppliedTaxInterface[] | null
     * @since 2.0.0
     */
    public function getAppliedTaxes();

    /**
     * Set applied taxes
     *
     * @param \Magento\Tax\Api\Data\AppliedTaxInterface[] $appliedTaxes
     * @return $this
     * @since 2.0.0
     */
    public function setAppliedTaxes(array $appliedTaxes = null);

    /**
     * Get TaxDetails items
     *
     * @return \Magento\Tax\Api\Data\TaxDetailsItemInterface[] | null
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Set TaxDetails items
     *
     * @param \Magento\Tax\Api\Data\TaxDetailsItemInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items = null);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Tax\Api\Data\TaxDetailsExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Tax\Api\Data\TaxDetailsExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxDetailsExtensionInterface $extensionAttributes);
}
