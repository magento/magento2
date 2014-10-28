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
namespace Magento\Backend\Model\Config\Structure\Element\Dependency;

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
    public function __construct(array $fieldData = array(), $fieldPrefix = "")
    {
        if (isset($fieldData['separator'])) {
            $this->_values = explode($fieldData['separator'], $fieldData['value']);
        } else {
            $this->_values = array($fieldData['value']);
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
