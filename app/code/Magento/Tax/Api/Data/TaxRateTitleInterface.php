<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

/**
 * Tax rate title interface.
 * @api
 * @since 2.0.0
 */
interface TaxRateTitleInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get store id
     *
     * @return string
     * @since 2.0.0
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @param string $storeId
     * @return $this
     * @since 2.0.0
     */
    public function setStoreId($storeId);

    /**
     * Get title value
     *
     * @return string
     * @since 2.0.0
     */
    public function getValue();

    /**
     * Set title value
     *
     * @param string $value
     * @return string
     * @since 2.0.0
     */
    public function setValue($value);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Tax\Api\Data\TaxRateTitleExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Tax\Api\Data\TaxRateTitleExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxRateTitleExtensionInterface $extensionAttributes);
}
