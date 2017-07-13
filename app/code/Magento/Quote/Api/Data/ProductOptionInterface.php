<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Product option interface
 * @api
 */
interface ProductOptionInterface extends ExtensibleDataInterface
{
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\ProductOptionExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\ProductOptionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Quote\Api\Data\ProductOptionExtensionInterface $extensionAttributes
    );
}
