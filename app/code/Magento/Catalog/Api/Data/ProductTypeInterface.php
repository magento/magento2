<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Product type details
 * @api
 * @since 2.0.0
 */
interface ProductTypeInterface extends ExtensibleDataInterface
{
    /**
     * Get product type code
     *
     * @return string
     * @since 2.0.0
     */
    public function getName();

    /**
     * Set product type code
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setName($name);

    /**
     * Get product type label
     *
     * @return string
     * @since 2.0.0
     */
    public function getLabel();

    /**
     * Set product type label
     *
     * @param string $label
     * @return $this
     * @since 2.0.0
     */
    public function setLabel($label);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Catalog\Api\Data\ProductTypeExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductTypeExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductTypeExtensionInterface $extensionAttributes
    );
}
