<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure\Element\Dependency;

/**
 * @api
 * @since 100.0.2
 */

/**
 * Class Field
 *
 * Fields are used to describe possible values for a type/interface.
 */
class Field
{
    /**
     * Values for dependence
     *
     * @var string[]
     */
    protected $_values;

    /**
     * Id of the dependent field
     *
     * @var string
     */
    protected $_id;

    /**
     * Whether dependence is for negative comparison
     *
     * @var bool
     */
    protected $_isNegative = false;

    /**
     * @param array $fieldData
     * @param string $fieldPrefix
     */
    public function __construct(array $fieldData = [], $fieldPrefix = "")
    {
        if (isset($fieldData['separator'])) {
            $this->_values = explode($fieldData['separator'], $fieldData['value']);
        } else {
            $this->_values = [isset($fieldData['value']) ? $fieldData['value'] : ''];
        }
        $fieldId = $fieldPrefix . (isset(
            $fieldData['dependPath']
        ) && is_array(
            $fieldData['dependPath']
        ) ? array_pop(
            $fieldData['dependPath']
        ) : '');
        $fieldData['dependPath'][] = $fieldId;
        $this->_id = implode('_', $fieldData['dependPath']);
        $this->_isNegative = isset($fieldData['negative']) && $fieldData['negative'];
    }

    /**
     * Check whether the value satisfy dependency
     *
     * @param string $value
     * @return bool
     */
    public function isValueSatisfy($value)
    {
        return in_array($value, $this->_values) xor $this->_isNegative;
    }

    /**
     * Get id of the dependent field
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Get values for dependence
     *
     * @return string[]
     */
    public function getValues()
    {
        return $this->_values;
    }

    /**
     * Get negative indication of dependency
     *
     * @return bool
     */
    public function isNegative()
    {
        return $this->_isNegative;
    }
}
