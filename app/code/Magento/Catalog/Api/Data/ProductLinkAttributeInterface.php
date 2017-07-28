<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 * @since 2.0.0
 */
interface ProductLinkAttributeInterface extends ExtensibleDataInterface
{
    /**
     * Get attribute code
     *
     * @return string
     * @since 2.0.0
     */
    public function getCode();

    /**
     * Set attribute code
     *
     * @param string $code
     * @return $this
     * @since 2.0.0
     */
    public function setCode($code);

    /**
     * Get attribute type
     *
     * @return string
     * @since 2.0.0
     */
    public function getType();

    /**
     * Set attribute type
     *
     * @param string $type
     * @return $this
     * @since 2.0.0
     */
    public function setType($type);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Catalog\Api\Data\ProductLinkAttributeExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductLinkAttributeExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductLinkAttributeExtensionInterface $extensionAttributes
    );
}
