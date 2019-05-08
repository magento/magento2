<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\GraphQl;

/**
 * Data object converter.
 */
class DataObjectConverter
{
    /**
     * Converts an input string from snake_case to camelCase.
     *
     * @param string $input
     * @return string
     */
    public static function snakeCaseToCamelCase($input)
    {
        return lcfirst(str_replace('_', '', ucwords($input, '_')));
    }

    /**
     * Convert a CamelCase string read from method into field key in snake_case
     *
     * @param string $name
     * @return string
     */
    public static function camelCaseToSnakeCase($name)
    {
        return strtolower(preg_replace('/(.)([A-Z0-9])/', "$1_$2", $name));
    }
}
