<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api;

use Magento\Framework\Convert\ConvertArray;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Data object converter.
 */
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
            $fieldName = lcfirst(str_replace('_', '', ucwords($fieldName, '_')));
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
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function convertStdObjectToArray($input, $removeItemNode = false)
    {
        if (!is_object($input) && !is_array($input)) {
            throw new \InvalidArgumentException("Input argument must be an array or object");
        }
        // @codingStandardsIgnoreStart
        if ($removeItemNode && (isset($input->item) || isset($input->Map))) {
            $node = isset($input->item) ? $input->item : $input->Map;
            /**
             * In case when only one Data object value is passed, it will not be wrapped into a subarray
             * within any additional node. If several Data object values are passed, they will be wrapped into
             * an indexed array within item or Map node.
             */
            $input = is_object($node) ? [$node] : $node;
        }
        // @codingStandardsIgnoreEnd
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
        return $input !== null ? str_replace('_', '', ucwords($input, '_')) : '';
    }

    /**
     * Converts an input string from snake_case to camelCase.
     *
     * @param string $input
     * @return string
     */
    public static function snakeCaseToCamelCase($input)
    {
        return lcfirst(self::snakeCaseToUpperCamelCase($input));
    }

    /**
     * Convert a CamelCase string read from method into field key in snake_case
     *
     * For example [DefaultShipping => default_shipping, Postcode => postcode]
     *
     * @param string $name
     * @return string
     */
    public static function camelCaseToSnakeCase($name)
    {
        return $name !== null ? strtolower(ltrim(preg_replace('/([A-Z])/m', "_$1", $name), '_')) : '';
    }
}
