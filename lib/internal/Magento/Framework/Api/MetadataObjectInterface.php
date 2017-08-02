<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Provides metadata about an attribute.
 *
 * @api
 * @since 2.0.0
 */
interface MetadataObjectInterface
{
    /**
     * Retrieve code of the attribute.
     *
     * @return string
     * @since 2.0.0
     */
    public function getAttributeCode();

    /**
     * Set code of the attribute.
     *
     * @param string $attributeCode
     * @return $this
     * @since 2.0.0
     */
    public function setAttributeCode($attributeCode);
}
