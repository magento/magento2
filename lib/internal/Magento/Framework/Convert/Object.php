<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Default converter for \Magento\Framework\Objects to arrays
 *
 * @author     Magento Extensibility Team <DL-X-Extensibility-Team@corp.ebay.com>
 */
namespace Magento\Framework\Convert;

class Object
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
        $result = array();
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
     * Converts a \Magento\Framework\Object into an array, including any children objects
     *
     * @param mixed $obj array or object to convert
     * @param array $objects array of object hashes used for cycle detection
     * @return array|string Converted object or CYCLE_DETECTED_MARK
     */
    protected function _convertObjectToArray($obj, &$objects = array())
    {
        $data = array();
        if (is_object($obj)) {
            $hash = spl_object_hash($obj);
            if (!empty($objects[$hash])) {
                return self::CYCLE_DETECTED_MARK;
            }
            $objects[$hash] = true;
            if ($obj instanceof \Magento\Framework\Object) {
                $data = $obj->getData();
            } else {
                $data = (array)$obj;
            }
        } else if (is_array($obj)) {
            $data = $obj;
        }

        $result = array();
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $result[$key] = $value;
            } else if (is_array($value)) {
                $result[$key] = $this->_convertObjectToArray($value, $objects);
            } else if ($value instanceof \Magento\Framework\Object) {
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
        $options = array();
        foreach ($items as $item) {
            $options[] = array(
                'value' => $this->_invokeGetter($item, $idField),
                'label' => $this->_invokeGetter($item, $valueField)
            );
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
        $options = array();
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
