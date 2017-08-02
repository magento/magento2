<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Product option interface
 * @api
 * @since 2.0.0
 */
interface ProductOptionInterface extends ExtensibleDataInterface
{
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductOptionExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductOptionExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductOptionExtensionInterface $extensionAttributes
    );
}
