<?php
/**
 * Data Model implementing the Address interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Data;

use Magento\SalesRule\Api\Data\ConditionInterface;

/**
 * Class Condition
 *
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class Condition extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Magento\SalesRule\Api\Data\ConditionInterface
{
    const KEY_CONDITION_TYPE = 'condition_type';
    const KEY_CONDITIONS = 'conditions';
    const KEY_AGGREGATOR_TYPE = 'aggregator_type';
    const KEY_OPERATOR = 'operator';
    const KEY_ATTRIBUTE_NAME = 'attribute_name';
    const KEY_VALUE = 'value';

    /**
     * Get condition type
     *
     * @return string
     * @since 2.0.0
     */
    public function getConditionType()
    {
        return $this->_get(self::KEY_CONDITION_TYPE);
    }

    /**
     * @param string $conditionType
     * @return $this
     * @since 2.0.0
     */
    public function setConditionType($conditionType)
    {
        return $this->setData(self::KEY_CONDITION_TYPE, $conditionType);
    }

    /**
     * Return list of conditions
     *
     * @return ConditionInterface[]|null
     * @since 2.0.0
     */
    public function getConditions()
    {
        return $this->_get(self::KEY_CONDITIONS);
    }

    /**
     * Set conditions
     *
     * @param ConditionInterface[]|null $conditions
     * @return $this
     * @since 2.0.0
     */
    public function setConditions(array $conditions = null)
    {
        return $this->setData(self::KEY_CONDITIONS, $conditions);
    }

    /**
     * Return the aggregator type
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getAggregatorType()
    {
        return $this->_get(self::KEY_AGGREGATOR_TYPE);
    }

    /**
     * Set the aggregator type
     *
     * @param string $aggregatorType
     * @return $this
     * @since 2.0.0
     */
    public function setAggregatorType($aggregatorType)
    {
        return $this->setData(self::KEY_AGGREGATOR_TYPE, $aggregatorType);
    }

    /**
     * Return the operator of the condition
     *
     * @return string
     * @since 2.0.0
     */
    public function getOperator()
    {
        return $this->_get(self::KEY_OPERATOR);
    }

    /**
     * Set the operator of the condition
     *
     * @param string $operator
     * @return $this
     * @since 2.0.0
     */
    public function setOperator($operator)
    {
        return $this->setData(self::KEY_OPERATOR, $operator);
    }

    /**
     * Return the attribute name of the condition
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getAttributeName()
    {
        return $this->_get(self::KEY_ATTRIBUTE_NAME);
    }

    /**
     * Set the attribute name of the condition
     *
     * @param string $attributeName
     * @return $this
     * @since 2.0.0
     */
    public function setAttributeName($attributeName)
    {
        return $this->setData(self::KEY_ATTRIBUTE_NAME, $attributeName);
    }

    /**
     * Return the value of the condition
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getValue()
    {
        return $this->_get(self::KEY_VALUE);
    }

    /**
     * Return the value of the condition
     *
     * @param mixed $value
     * @return $this
     * @since 2.0.0
     */
    public function setValue($value)
    {
        return $this->setData(self::KEY_VALUE, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\SalesRule\Api\Data\ConditionExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\SalesRule\Api\Data\ConditionExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\SalesRule\Api\Data\ConditionExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
