<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

/**
 * Base Class for simple data Objects
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 2.0.0
 */
abstract class AbstractDataObject
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $data;

    /**
     * Return Data Object data in array format.
     *
     * @return array
     * @since 2.0.0
     */
    public function toArray()
    {
        $data = $this->data;
        $hasToArray = function ($model) {
            return is_object($model) && method_exists($model, 'toArray') && is_callable([$model, 'toArray']);
        };
        foreach ($data as $key => $value) {
            if ($hasToArray($value)) {
                $data[$key] = $value->toArray();
            } elseif (is_array($value)) {
                foreach ($value as $nestedKey => $nestedValue) {
                    if ($hasToArray($nestedValue)) {
                        $value[$nestedKey] = $nestedValue->toArray();
                    }
                }
                $data[$key] = $value;
            }
        }
        return $data;
    }

    /**
     * Retrieves a value from the data array if set, or null otherwise.
     *
     * @param string $key
     * @return mixed|null
     * @since 2.0.0
     */
    protected function get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }
}
