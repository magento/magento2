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
 * @since 100.0.2
 */
interface TaxRateTitleInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get store id
     *
     * @return string
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @param string $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get title value
     *
     * @return string
     */
    public function getValue();

    /**
     * Set title value
     *
     * @param string $value
     * @return string
     */
    public function setValue($value);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Tax\Api\Data\TaxRateTitleExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Tax\Api\Data\TaxRateTitleExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxRateTitleExtensionInterface $extensionAttributes);
}
