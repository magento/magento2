<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */
interface ProductLinkAttributeInterface extends ExtensibleDataInterface
{
    /**
     * Get attribute code
     *
     * @return string
     */
    public function getCode();

    /**
     * Set attribute code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Get attribute type
     *
     * @return string
     */
    public function getType();

    /**
     * Set attribute type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Catalog\Api\Data\ProductLinkAttributeExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductLinkAttributeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductLinkAttributeExtensionInterface $extensionAttributes
    );
}
