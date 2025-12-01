<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
