<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Interface Attribute Type Resolver
 *
 * @api
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
     */
    public function resolveObjectType($attributeCode, $value, $context);
}
