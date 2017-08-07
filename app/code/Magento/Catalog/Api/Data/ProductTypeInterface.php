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
 */
interface ProductTypeInterface extends ExtensibleDataInterface
{
    /**
     * Get product type code
     *
     * @return string
     */
    public function getName();

    /**
     * Set product type code
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get product type label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set product type label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Catalog\Api\Data\ProductTypeExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductTypeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductTypeExtensionInterface $extensionAttributes
    );
}
