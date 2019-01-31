<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Default converter for \Magento\Framework\DataObjects to arrays
 *
 * @api
 */
namespace Magento\Framework\Convert;

class DataObject
{
    /** Constant used to mark cycles in the input array/objects */
    const CYCLE_DETECTED_MARK = '*** CYCLE DETECTED ***';

    /**
     * Convert input data into an array and return the resulting array.
     * The resulting array should not contain any objects.
     *
     * @param array $data input data
     * @return array Data converted to an array
     */
    public function convertDataToArray($data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_object($value) || is_array($value)) {
                $result[$key] = $this->_convertObjectToArray($value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * Converts a \Magento\Framework\DataObject into an array, including any children objects
     *
     * @param mixed $obj array or object to convert
     * @param array $objects array of object hashes used for cycle detection
     * @return array|string Converted object or CYCLE_DETECTED_MARK
     */
    protected function _convertObjectToArray($obj, &$objects = [])
    {
        $data = [];
        if (is_object($obj)) {
            $hash = spl_object_hash($obj);
            if (!empty($objects[$hash])) {
                return self::CYCLE_DETECTED_MARK;
            }
            $objects[$hash] = true;
            if ($obj instanceof \Magento\Framework\DataObject) {
                $data = $obj->getData();
            } else {
                $data = (array)$obj;
            }
        } elseif (is_array($obj)) {
            $data = $obj;
        }

        $result = [];
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $result[$key] = $value;
            } elseif (is_array($value)) {
                $result[$key] = $this->_convertObjectToArray($value, $objects);
            } elseif ($value instanceof \Magento\Framework\DataObject) {
                $result[$key] = $this->_convertObjectToArray($value, $objects);
            }
        }
        return $result;
    }

    /**
     * Converts the list of objects into an array of the form: [ [ 'label' => <id>, 'value' => <value> ], ... ].
     *
     *
     * The <id> and <value> values are taken from the objects in the list using the $idField and $valueField
     * parameters, which can be either the name of the field to use, or a closure.
     *
     * @param array $items
     * @param string|callable $idField
     * @param string|callable $valueField
     * @return array
     */
    public function toOptionArray(array $items, $idField, $valueField)
    {
        $options = [];
        foreach ($items as $item) {
            $options[] = [
                'value' => $this->_invokeGetter($item, $idField),
                'label' => $this->_invokeGetter($item, $valueField),
            ];
        }
        return $options;
    }

    /**
     * Converts the list of objects into an array of the form: [ <id> => <value>, ... ].
     *
     *
     * The <id> and <value> values are taken from the objects in the list using the $idField and $valueField parameters,
     * which can be either the name of the field to use, or a closure.
     *
     * @param array $items
     * @param string|callable $idField
     * @param string|callable $valueField
     * @return array
     */
    public function toOptionHash(array $items, $idField, $valueField)
    {
        $options = [];
        foreach ($items as $item) {
            $options[$this->_invokeGetter($item, $idField)] = $this->_invokeGetter($item, $valueField);
        }
        return $options;
    }

    /**
     * Returns the value of the property represented by $field on the $item object.
     *
     *
     * When $field is a closure, the $item parameter is passed to the $field method, otherwise the $field is assumed
     * to be a property name, and the associated get method is invoked on the $item instead.
     *
     * @param mixed $item
     * @param string|callable $field
     * @return mixed
     */
    protected function _invokeGetter($item, $field)
    {
        if (is_callable($field)) {
            // if $field is a closure, use that on the item
            return $field($item);
        } else {
            // otherwise, turn it into a call to the item's getter method
            $methodName = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
            return $item->{$methodName}();
        }
    }
}
