<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Api\Data;

/**
 * Object that contains quantity information for a single associated product of a Grouped Product
 *
 * Interface \Magento\GroupedProduct\Api\Data\GroupedItemQtyInterface
 */
interface GroupedItemQtyInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const PRODUCT_ID = 'product_id';
    const QTY        = 'qty';

    /**
     * Set associated product id
     *
     * @param int|string $value
     */
    public function setProductId($value);

    /**
     * Get associated product id
     *
     * @return int|string
     */
    public function getProductId();

    /**
     * Set associated product qty
     *
     * @param int|string $qty
     */
    public function setQty($qty);

    /**
     * Get associated product qty
     *
     * @return int
     */
    public function getQty();

    /**
     * Set extension attributes
     *
     * @param \Magento\GroupedProduct\Api\Data\GroupedItemQtyExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\GroupedProduct\Api\Data\GroupedItemQtyExtensionInterface $extensionAttributes
    );

    /**
     * Get extension attributes
     *
     * @return \Magento\GroupedProduct\Api\Data\GroupedItemQtyExtensionInterface|null
     */
    public function getExtensionAttributes();
}
