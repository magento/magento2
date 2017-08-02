<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api;

/**
 * Base Class for simple data Objects
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 2.0.0
 */
abstract class AbstractSimpleObject
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $_data;

    /**
     * Initialize internal storage
     *
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(array $data = [])
    {
        $this->_data = $data;
    }

    /**
     * Retrieves a value from the data array if set, or null otherwise.
     *
     * @param string $key
     * @return mixed|null
     * @since 2.0.0
     */
    protected function _get($key)
    {
        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }

    /**
     * Set value for the given key
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     * @since 2.0.0
     */
    public function setData($key, $value)
    {
        $this->_data[$key] = $value;
        return $this;
    }

    /**
     * Return Data Object data in array format.
     *
     * @return array
     * @since 2.0.0
     */
    public function __toArray()
    {
        $data = $this->_data;
        $hasToArray = function ($model) {
            return is_object($model) && method_exists($model, '__toArray') && is_callable([$model, '__toArray']);
        };
        foreach ($data as $key => $value) {
            if ($hasToArray($value)) {
                $data[$key] = $value->__toArray();
            } elseif (is_array($value)) {
                foreach ($value as $nestedKey => $nestedValue) {
                    if ($hasToArray($nestedValue)) {
                        $value[$nestedKey] = $nestedValue->__toArray();
                    }
                }
                $data[$key] = $value;
            }
        }
        return $data;
    }
}
