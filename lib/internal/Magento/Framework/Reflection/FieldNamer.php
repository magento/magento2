<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Reflection;

use Magento\Framework\Api\SimpleDataObjectConverter;

/**
 * Determines the name to use for fields in a data output array given method metadata.
 */
class FieldNamer
{
    public const IS_METHOD_PREFIX = 'is';
    public const HAS_METHOD_PREFIX = 'has';
    public const GETTER_PREFIX = 'get';

    /**
     * Converts a method's name into a data field name.
     *
     * @param string $methodName
     * @return string|null
     */
    public function getFieldNameForMethodName($methodName)
    {
        $methodName = $methodName ?? ''; // @phpstan-ignore-line
        if (substr($methodName, 0, 2) === self::IS_METHOD_PREFIX) {
            return SimpleDataObjectConverter::camelCaseToSnakeCase(substr($methodName, 2));
        } elseif (substr($methodName, 0, 3) === self::HAS_METHOD_PREFIX) {
            return SimpleDataObjectConverter::camelCaseToSnakeCase(substr($methodName, 3));
        } elseif (substr($methodName, 0, 3) === self::GETTER_PREFIX) {
            return SimpleDataObjectConverter::camelCaseToSnakeCase(substr($methodName, 3));
        }

        return null;
    }
}
