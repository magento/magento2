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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Service\Data;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractObjectBuilder
{
    /**
     * @var array
     */
    protected $_data;

    /**
     * Initialize internal storage
     */
    public function __construct()
    {
        $this->_data = array();
    }

    /**
     * Populates the fields with an existing entity.
     *
     * @param AbstractObject $prototype the prototype to base on
     * @return $this
     * @throws \LogicException If $prototype object class is not the same type as object that is constructed
     */
    public function populate(AbstractObject $prototype)
    {
        $objectType = $this->_getDataObjectType();
        if (get_class($prototype) != $objectType) {
            throw new \LogicException('Wrong prototype object given. It can only be of "' . $objectType . '" type.');
        }
        return $this->populateWithArray($prototype->__toArray());
    }

    /**
     * Template method used to configure the attribute codes for the custom attributes
     *
     * @return array
     */
    public function getCustomAttributesCodes()
    {
        return array();
    }

    /**
     * Populates the fields with data from the array.
     *
     * Keys for the map are snake_case attribute/field names.
     *
     * @param array $data
     * @return $this
     */
    public function populateWithArray(array $data)
    {
        $this->_data = array();
        $this->_setDataValues($data);
        return $this;
    }

    /**
     * Initializes Data Object with the data from array
     *
     * @param array $data
     * @return $this
     */
    protected function _setDataValues(array $data)
    {
        $dataObjectMethods = get_class_methods($this->_getDataObjectType());
        foreach ($data as $key => $value) {
            /* First, verify is there any getter for the key on the Service Data Object */
            $possibleMethods = array(
                'get' . $this->_snakeCaseToCamelCase($key),
                'is' . $this->_snakeCaseToCamelCase($key)
            );
            if (array_intersect($possibleMethods, $dataObjectMethods)) {
                $this->_data[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * Merge second Data Object data with first Data Object data and create new Data Object object based on merge
     * result.
     *
     * @param AbstractObject $firstDataObject
     * @param AbstractObject $secondDataObject
     * @return AbstractObject
     * @throws \LogicException
     */
    public function mergeDataObjects(AbstractObject $firstDataObject, AbstractObject $secondDataObject)
    {
        $objectType = $this->_getDataObjectType();
        if (get_class($firstDataObject) != $objectType || get_class($secondDataObject) != $objectType) {
            throw new \LogicException('Wrong prototype object given. It can only be of "' . $objectType . '" type.');
        }
        $this->_setDataValues($firstDataObject->__toArray());
        $this->_setDataValues($secondDataObject->__toArray());
        return $this->create();
    }

    /**
     * Merged data provided in array format with Data Object data and create new Data Object object based on merge
     * result.
     *
     * @param AbstractObject $dataObject
     * @param array $data
     * @return AbstractObject
     * @throws \LogicException
     */
    public function mergeDataObjectWithArray(AbstractObject $dataObject, array $data)
    {
        $objectType = $this->_getDataObjectType();
        if (get_class($dataObject) != $objectType) {
            throw new \LogicException('Wrong prototype object given. It can only be of "' . $objectType . '" type.');
        }
        $this->_setDataValues($dataObject->__toArray());
        $this->_setDataValues($data);
        return $this->create();
    }

    /**
     * Builds the Data Object
     *
     * @return AbstractObject
     */
    public function create()
    {
        $dataObjectType = $this->_getDataObjectType();
        $dataObject = new $dataObjectType($this);
        $this->_data = array();
        return $dataObject;
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     */
    protected function _set($key, $value)
    {
        $this->_data[$key] = $value;
        return $this;
    }

    /**
     * Return the Data type class name
     *
     * @return string
     */
    protected function _getDataObjectType()
    {
        return substr(get_class($this), 0, -7);
    }

    /**
     * Converts an input string from snake_case to upper CamelCase.
     *
     * @param string $input
     * @return string
     */
    protected function _snakeCaseToCamelCase($input)
    {
        $output = '';
        $segments = explode('_', $input);
        foreach ($segments as $segment) {
            $output .= ucfirst($segment);
        }
        return $output;
    }

    /**
     * Return data Object data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }
}
