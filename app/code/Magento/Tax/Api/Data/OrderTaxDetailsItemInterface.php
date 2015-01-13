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
     * Return item id if this item is a product
     *
     * @return int|null
     */
    public function getItemId();

    /**
     * Return associated item id if this item is associated with another item, null otherwise
     *
     * @return int|null
     */
    public function getAssociatedItemId();

    /**
     * Get applied taxes
     *
     * @return \Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface[]|null
     */
    public function getAppliedTaxes();
}
