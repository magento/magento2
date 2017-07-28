<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

/**
 * Interface OrderTaxDetailsInterface
 * @api
 * @since 2.0.0
 */
interface OrderTaxDetailsInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get applied taxes at order level
     *
     * @return \Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface[] | null
     * @since 2.0.0
     */
    public function getAppliedTaxes();

    /**
     * Set applied taxes at order level
     *
     * @param \Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface[] $appliedTaxes
     * @return $this
     * @since 2.0.0
     */
    public function setAppliedTaxes(array $appliedTaxes = null);

    /**
     * Get order item tax details
     *
     * @return \Magento\Tax\Api\Data\OrderTaxDetailsItemInterface[] | null
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Set order item tax details
     *
     * @param \Magento\Tax\Api\Data\OrderTaxDetailsItemInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items = null);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Tax\Api\Data\OrderTaxDetailsExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Tax\Api\Data\OrderTaxDetailsExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Tax\Api\Data\OrderTaxDetailsExtensionInterface $extensionAttributes
    );
}
