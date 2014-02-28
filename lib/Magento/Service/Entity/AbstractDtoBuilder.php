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

namespace Magento\Service\Entity;

abstract class AbstractDtoBuilder
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
     * @param AbstractDto $prototype the prototype to base on
     * @return $this
     */
    public function populate(AbstractDto $prototype)
    {
        return $this->populateWithArray($prototype->__toArray());
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
        $this->_data = [];
        $dtoMethods = get_class_methods(get_class($this));
        foreach ($data as $key => $value) {
            $method = 'set' . $this->_snakeCaseToCamelCase($key);
            if (in_array($method, $dtoMethods)) {
                $this->$method($value);
            } else {
                $this->_data[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Merge second DTO data with first DTO data and create new DTO object based on merge result.
     *
     * @param AbstractDto $firstDto
     * @param AbstractDto $secondDto
     * @return AbstractDto
     */
    public function mergeDtos(AbstractDto $firstDto, AbstractDto $secondDto)
    {
        $this->_data = array_merge($firstDto->__toArray(), $secondDto->__toArray());
        return $this->create();
    }

    /**
     * Merged data provided in array format with DTO data and create new DTO object based on merge result.
     *
     * @param AbstractDto $dto
     * @param array $data
     * @return AbstractDto
     */
    public function mergeDtoWithArray(AbstractDto $dto, array $data)
    {
        $this->_data = array_merge($dto->__toArray(), $data);
        return $this->create();
    }

    /**
     * Builds the entity.
     *
     * @return AbstractDto
     */
    public function create()
    {
        $dtoType = $this->_getDtoType();
        $retObj = new $dtoType($this->_data);
        $this->_data = array();
        return $retObj;
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
     * Return the Dto type class name
     *
     * @return string
     */
    protected function _getDtoType()
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
}
