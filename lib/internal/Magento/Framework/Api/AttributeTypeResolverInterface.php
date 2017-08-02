<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Interface \Magento\Framework\Api\AttributeTypeResolverInterface
 *
 * @since 2.0.0
 */
interface AttributeTypeResolverInterface
{
    /**
     * Resolve attribute type
     *
     * @param string $attributeCode
     * @param object $value
     * @param string $context
     * @return string
     * @since 2.0.0
     */
    public function resolveObjectType($attributeCode, $value, $context);
}
