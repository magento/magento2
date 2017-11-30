<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Webapi\CustomAttribute;

/**
 * Interface to locate types for custom attributes
 */
interface TypeLocatorInterface
{
    /**
     * Get Data Interface type for a given custom attribute code
     *
     * @param string $attributeCode
     * @param string $entityType
     * @return string
     */
    public function getType($attributeCode, $entityType);
}
