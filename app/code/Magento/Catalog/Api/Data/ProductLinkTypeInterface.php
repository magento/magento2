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
interface ProductLinkTypeInterface extends ExtensibleDataInterface
{
    /**
     * Get link type code
     *
     * @return int
     * @since 2.0.0
     */
    public function getCode();

    /**
     * Set link type code
     *
     * @param int $code
     * @return $this
     * @since 2.0.0
     */
    public function setCode($code);

    /**
     * Get link type name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName();

    /**
     * Set link type name
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setName($name);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Catalog\Api\Data\ProductLinkTypeExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductLinkTypeExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductLinkTypeExtensionInterface $extensionAttributes
    );
}
