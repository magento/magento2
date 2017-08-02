<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

/**
 * Interface OrderTaxDetailsItemInterface
 * @api
 * @since 2.0.0
 */
interface OrderTaxDetailsItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get type (shipping, product, weee, gift wrapping, etc)
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getType();

    /**
     * Set type (shipping, product, weee, gift wrapping, etc)
     *
     * @param string $type
     * @return $this
     * @since 2.0.0
     */
    public function setType($type);

    /**
     * Return item id if this item is a product
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getItemId();

    /**
     * Set item id
     *
     * @param int $itemId
     * @return $this
     * @since 2.0.0
     */
    public function setItemId($itemId);

    /**
     * Return associated item id if this item is associated with another item, null otherwise
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getAssociatedItemId();

    /**
     * Set associated item id
     *
     * @param int $associatedItemId
     * @return $this
     * @since 2.0.0
     */
    public function setAssociatedItemId($associatedItemId);

    /**
     * Get applied taxes
     *
     * @return \Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface[]|null
     * @since 2.0.0
     */
    public function getAppliedTaxes();

    /**
     * Set applied taxes
     *
     * @param \Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface[] $appliedTaxes
     * @return $this
     * @since 2.0.0
     */
    public function setAppliedTaxes(array $appliedTaxes = null);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Tax\Api\Data\OrderTaxDetailsItemExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Tax\Api\Data\OrderTaxDetailsItemExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Tax\Api\Data\OrderTaxDetailsItemExtensionInterface $extensionAttributes
    );
}
