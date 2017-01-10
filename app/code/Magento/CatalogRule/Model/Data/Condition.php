<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\Data;

use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Class Condition
 * @codeCoverageIgnore
 */
class Condition extends AbstractExtensibleModel implements \Magento\CatalogRule\Api\Data\ConditionInterface
{
    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute($attribute)
    {
        return $this->setData(self::ATTRIBUTE, $attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute()
    {
        return $this->getData(self::ATTRIBUTE);
    }

    /**
     * {@inheritdoc}
     */
    public function setOperator($operator)
    {
        return $this->setData(self::OPERATOR, $operator);
    }

    /**
     * {@inheritdoc}
     */
    public function getOperator()
    {
        return $this->getData(self::OPERATOR);
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsValueParsed($isValueParsed)
    {
        return $this->setData(self::IS_VALUE_PARSED, $isValueParsed);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsValueParsed()
    {
        return $this->getData(self::IS_VALUE_PARSED);
    }

    /**
     * {@inheritdoc}
     */
    public function setAggregator($aggregator)
    {
        return $this->setData(self::AGGREGATOR, $aggregator);
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregator()
    {
        return $this->getData(self::AGGREGATOR);
    }

    /**
     * {@inheritdoc}
     */
    public function setConditions($conditions)
    {
        return $this->setData(self::CONDITIONS, $conditions);
    }

    /**
     * {@inheritdoc}
     */
    public function getConditions()
    {
        return $this->getData(self::CONDITIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \Magento\CatalogRule\Api\Data\ConditionExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
