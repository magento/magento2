<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\Data;

use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Class Condition
 * @codeCoverageIgnore
 * @since 2.1.0
 */
class Condition extends AbstractExtensibleModel implements \Magento\CatalogRule\Api\Data\ConditionInterface
{
    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function setAttribute($attribute)
    {
        return $this->setData(self::ATTRIBUTE, $attribute);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getAttribute()
    {
        return $this->getData(self::ATTRIBUTE);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function setOperator($operator)
    {
        return $this->setData(self::OPERATOR, $operator);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getOperator()
    {
        return $this->getData(self::OPERATOR);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function setIsValueParsed($isValueParsed)
    {
        return $this->setData(self::IS_VALUE_PARSED, $isValueParsed);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getIsValueParsed()
    {
        return $this->getData(self::IS_VALUE_PARSED);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function setAggregator($aggregator)
    {
        return $this->setData(self::AGGREGATOR, $aggregator);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getAggregator()
    {
        return $this->getData(self::AGGREGATOR);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function setConditions($conditions)
    {
        return $this->setData(self::CONDITIONS, $conditions);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getConditions()
    {
        return $this->getData(self::CONDITIONS);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function setExtensionAttributes(
        \Magento\CatalogRule\Api\Data\ConditionExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
