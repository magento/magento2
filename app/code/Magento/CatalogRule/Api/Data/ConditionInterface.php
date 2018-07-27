<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Api\Data;

/**
 * @api
 */
interface ConditionInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const TYPE = 'type';

    const ATTRIBUTE = 'attribute';

    const OPERATOR = 'operator';

    const VALUE = 'value';

    const IS_VALUE_PARSED = 'is_value_parsed';

    const AGGREGATOR = 'aggregator';

    const CONDITIONS = 'conditions';
    /**#@-*/

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $attribute
     * @return $this
     */
    public function setAttribute($attribute);

    /**
     * @return string
     */
    public function getAttribute();

    /**
     * @param string $operator
     * @return $this
     */
    public function setOperator($operator);

    /**
     * @return string
     */
    public function getOperator();

    /**
     * @param string $value
     * @return $this
     */
    public function setValue($value);

    /**
     * @return string
     */
    public function getValue();

    /**
     * @param bool $isValueParsed
     * @return $this
     */
    public function setIsValueParsed($isValueParsed);

    /**
     * @return bool|null
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsValueParsed();

    /**
     * @param string $aggregator
     * @return $this
     */
    public function setAggregator($aggregator);

    /**
     * @return string
     */
    public function getAggregator();

    /**
     * @param \Magento\CatalogRule\Api\Data\ConditionInterface[] $conditions
     * @return $this
     */
    public function setConditions($conditions);

    /**
     * @return \Magento\CatalogRule\Api\Data\ConditionInterface[]|null
     */
    public function getConditions();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\CatalogRule\Api\Data\ConditionExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\CatalogRule\Api\Data\ConditionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\CatalogRule\Api\Data\ConditionExtensionInterface $extensionAttributes
    );
}
