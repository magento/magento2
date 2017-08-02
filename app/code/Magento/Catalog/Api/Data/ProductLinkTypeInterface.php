<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */
interface ProductLinkTypeInterface extends ExtensibleDataInterface
{
    /**
     * Get link type code
     *
     * @return int
     */
    public function getCode();

    /**
     * Set link type code
     *
     * @param int $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Get link type name
     *
     * @return string
     */
    public function getName();

    /**
     * Set link type name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Catalog\Api\Data\ProductLinkTypeExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductLinkTypeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductLinkTypeExtensionInterface $extensionAttributes
    );
}
