<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 * @since 2.0.0
 */
interface ProductTierPriceInterface extends ExtensibleDataInterface
{
    const QTY = 'qty';

    const VALUE = 'value';

    const CUSTOMER_GROUP_ID = 'customer_group_id';

    /**
     * Retrieve customer group id
     *
     * @return int
     * @since 2.0.0
     */
    public function getCustomerGroupId();

    /**
     * Set customer group id
     *
     * @param int $customerGroupId
     * @return $this
     * @since 2.0.0
     */
    public function setCustomerGroupId($customerGroupId);

    /**
     * Retrieve tier qty
     *
     * @return float
     * @since 2.0.0
     */
    public function getQty();

    /**
     * Set tier qty
     *
     * @param float $qty
     * @return $this
     * @since 2.0.0
     */
    public function setQty($qty);

    /**
     * Retrieve price value
     *
     * @return float
     * @since 2.0.0
     */
    public function getValue();

    /**
     * Set price value
     *
     * @param float $value
     * @return $this
     * @since 2.0.0
     */
    public function setValue($value);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Catalog\Api\Data\ProductTierPriceExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductTierPriceExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductTierPriceExtensionInterface $extensionAttributes
    );
}
