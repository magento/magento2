<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Api\Data;

/**
 * Interface ConditionInterface
 *
 * @api
 * @since 2.0.0
 */
interface ConditionInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const AGGREGATOR_TYPE_ALL = 'all';
    const AGGREGATOR_TYPE_ANY = 'any';

    /**
     * Get condition type
     *
     * @return string
     * @since 2.0.0
     */
    public function getConditionType();

    /**
     * @param string $conditionType
     * @return $this
     * @since 2.0.0
     */
    public function setConditionType($conditionType);

    /**
     * Return list of conditions
     *
     * @return \Magento\SalesRule\Api\Data\ConditionInterface[]|null
     * @since 2.0.0
     */
    public function getConditions();

    /**
     * Set conditions
     *
     * @param \Magento\SalesRule\Api\Data\ConditionInterface[]|null $conditions
     * @return $this
     * @since 2.0.0
     */
    public function setConditions(array $conditions = null);

    /**
     * Return the aggregator type
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getAggregatorType();

    /**
     * Set the aggregator type
     *
     * @param string $aggregatorType
     * @return $this
     * @since 2.0.0
     */
    public function setAggregatorType($aggregatorType);

    /**
     * Return the operator of the condition
     *
     * @return string
     * @since 2.0.0
     */
    public function getOperator();

    /**
     * Set the operator of the condition
     *
     * @param string $operator
     * @return $this
     * @since 2.0.0
     */
    public function setOperator($operator);

    /**
     * Return the attribute name of the condition
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getAttributeName();

    /**
     * Set the attribute name of the condition
     *
     * @param string $attributeName
     * @return $this
     * @since 2.0.0
     */
    public function setAttributeName($attributeName);

    /**
     * Return the value of the condition
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getValue();

    /**
     * Return the value of the condition
     *
     * @param mixed $value
     * @return $this
     * @since 2.0.0
     */
    public function setValue($value);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\SalesRule\Api\Data\ConditionExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\SalesRule\Api\Data\ConditionExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\SalesRule\Api\Data\ConditionExtensionInterface $extensionAttributes
    );
}
