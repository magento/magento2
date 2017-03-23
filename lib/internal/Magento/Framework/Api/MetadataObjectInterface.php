<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Provides metadata about an attribute.
 *
 * @api
 */
interface MetadataObjectInterface
{
    /**
     * Retrieve code of the attribute.
     *
     * @return string
     */
    public function getAttributeCode();

    /**
     * Set code of the attribute.
     *
     * @param string $attributeCode
     * @return $this
     */
    public function setAttributeCode($attributeCode);
}
