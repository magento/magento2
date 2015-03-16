<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

interface OrderTaxDetailsItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_TYPE = 'type';

    const KEY_ITEM_ID = 'item_id';

    const KEY_ASSOCIATED_ITEM_ID = 'associated_item_id';

    const KEY_APPLIED_TAXES = 'applied_taxes';
    /**#@-*/

    /**
     * Get type (shipping, product, weee, gift wrapping, etc)
     *
     * @return string|null
     */
    public function getType();

    /**
     * Set type (shipping, product, weee, gift wrapping, etc)
     *
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * Return item id if this item is a product
     *
     * @return int|null
     */
    public function getItemId();

    /**
     * Set item id
     *
     * @param int $itemId
     * @return $this
     */
    public function setItemId($itemId);

    /**
     * Return associated item id if this item is associated with another item, null otherwise
     *
     * @return int|null
     */
    public function getAssociatedItemId();

    /**
     * Set associated item id
     *
     * @param int $associatedItemId
     * @return $this
     */
    public function setAssociatedItemId($associatedItemId);

    /**
     * Get applied taxes
     *
     * @return \Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface[]|null
     */
    public function getAppliedTaxes();

    /**
     * Set applied taxes
     *
     * @param \Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface[] $appliedTaxes
     * @return $this
     */
    public function setAppliedTaxes(array $appliedTaxes = null);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Tax\Api\Data\OrderTaxDetailsItemExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Tax\Api\Data\OrderTaxDetailsItemExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Tax\Api\Data\OrderTaxDetailsItemExtensionInterface $extensionAttributes
    );
}
