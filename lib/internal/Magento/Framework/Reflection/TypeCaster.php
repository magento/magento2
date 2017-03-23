<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Reflection;

/**
 * Casts values to the type given.
 */
class TypeCaster
{
    /**
     * Cast the output type to the documented type. This helps for consistent output (e.g. JSON).
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function castValueToType($value, $type)
    {
        if ($value === null) {
            return null;
        }

        if ($type === "int" || $type === "integer") {
            return (int)$value;
        }

        if ($type === "string") {
            return (string)$value;
        }

        if ($type === "bool" || $type === "boolean" || $type === "true" || $type == "false") {
            return (bool)$value;
        }

        if ($type === "float") {
            return (float)$value;
        }

        if ($type === "double") {
            return (double)$value;
        }

        return $value;
    }
}
