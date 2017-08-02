<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Reflection;

use Magento\Framework\Serialize\Serializer\Json;

/**
 * Casts values to the type given.
 * @since 2.0.0
 */
class TypeCaster
{
    /**
     * @var Json
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @param Json $serializer
     * @since 2.2.0
     */
    public function __construct(Json $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Cast the output type to the documented type. This helps for consistent output (e.g. JSON).
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function castValueToType($value, $type)
    {
        if ($value === null) {
            return null;
        }

        /**
         * Type caster does not complicated arrays according to restrictions in JSON/SOAP API
         * but interface and class implementations should be processed as is.
         * Function `class_exists()` is called to do not break code which return an array instead
         * interface implementation.
         */
        if (is_array($value) && !interface_exists($type) && !class_exists($type)) {
            return $this->serializer->serialize($value);
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
