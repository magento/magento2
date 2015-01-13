<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api;

use Magento\Framework\Convert\ConvertArray;
use Magento\Framework\Reflection\DataObjectProcessor;

class SimpleDataObjectConverter
{
    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(DataObjectProcessor $dataObjectProcessor)
    {
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Convert nested array into flat array.
     *
     * @param ExtensibleDataInterface $dataObject
     * @param string $dataObjectType
     * @return array
     */
    public function toFlatArray(ExtensibleDataInterface $dataObject, $dataObjectType = null)
    {
        if ($dataObjectType === null) {
            $dataObjectType = get_class($dataObject);
        }
        $data = $this->dataObjectProcessor->buildOutputDataArray($dataObject, $dataObjectType);
        return ConvertArray::toFlatArray($data);
    }

    /**
     * Convert keys to camelCase
     *
     * @param array $dataArray
     * @return \stdClass
     */
    public function convertKeysToCamelCase(array $dataArray)
    {
        $response = [];
        if (isset($dataArray[AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY])) {
            $dataArray = ExtensibleDataObjectConverter::convertCustomAttributesToSequentialArray($dataArray);
        }
        foreach ($dataArray as $fieldName => $fieldValue) {
            if (is_array($fieldValue) && !$this->_isSimpleSequentialArray($fieldValue)) {
                $fieldValue = $this->convertKeysToCamelCase($fieldValue);
            }
            $fieldName = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName))));
            $response[$fieldName] = $fieldValue;
        }
        return $response;
    }

    /**
     * Check if the array is a simple(one dimensional and not nested) and a sequential(non-associative) array
     *
     * @param array $data
     * @return bool
     */
    protected function _isSimpleSequentialArray(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_string($key) || is_array($value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Convert multidimensional object/array into multidimensional array of primitives.
     *
     * @param object|array $input
     * @param bool $removeItemNode Remove Item node from arrays if true
     * @return array
     * @throws \InvalidArgumentException
     */
    public function convertStdObjectToArray($input, $removeItemNode = false)
    {
        if (!is_object($input) && !is_array($input)) {
            throw new \InvalidArgumentException("Input argument must be an array or object");
        }
        if ($removeItemNode && isset($input->item)) {
            /**
             * In case when only one Data object value is passed, it will not be wrapped into a subarray
             * within item node. If several Data object values are passed, they will be wrapped into
             * an indexed array within item node.
             */
            $input = is_object($input->item) ? [$input->item] : $input->item;
        }
        $result = [];
        foreach ((array)$input as $key => $value) {
            if (is_object($value) || is_array($value)) {
                $result[$key] = $this->convertStdObjectToArray($value, $removeItemNode);
            } else {
                $result[$key] = $value;
            }
        }
        return $this->_unpackAssociativeArray($result);
    }

    /**
     * Unpack associative array packed by SOAP server into key-value
     *
     * @param mixed $data
     * @return array Unpacked associative array if array was passed as argument or original value otherwise
     */
    protected function _unpackAssociativeArray($data)
    {
        if (!is_array($data)) {
            return $data;
        } else {
            foreach ($data as $key => $value) {
                if (is_array($value) && count($value) == 2 && isset($value['key']) && isset($value['value'])) {
                    $data[$value['key']] = $this->_unpackAssociativeArray($value['value']);
                    unset($data[$key]);
                } else {
                    $data[$key] = $this->_unpackAssociativeArray($value);
                }
            }
            return $data;
        }
    }

    /**
     * Converts an input string from snake_case to upper CamelCase.
     *
     * @param string $input
     * @return string
     */
    public static function snakeCaseToUpperCamelCase($input)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $input)));
    }

    /**
     * Converts an input string from snake_case to camelCase.
     *
     * @param string $input
     * @return string
     */
    public static function snakeCaseToCamelCase($input)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
    }

    /**
     * Convert a CamelCase string read from method into field key in snake_case
     *
     * e.g. DefaultShipping => default_shipping
     *      Postcode => postcode
     *
     * @param string $name
     * @return string
     */
    public static function camelCaseToSnakeCase($name)
    {
        return strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
    }
}
