<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Search;

use Magento\Framework\Api\AbstractSimpleObject;

/**
 * @api
 * @since 2.0.0
 */
class Document extends AbstractSimpleObject implements DocumentInterface, \IteratorAggregate
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCustomAttribute($attributeCode)
    {
        return isset($this->_data[self::CUSTOM_ATTRIBUTES][$attributeCode])
            ? $this->_data[self::CUSTOM_ATTRIBUTES][$attributeCode]
            : null;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setCustomAttribute($attributeCode, $attributeValue)
    {
        /** @var \Magento\Framework\Api\AttributeInterface[] $attributes */
        $attributes = $this->getCustomAttributes();
        $attributes[$attributeCode] = $attributeValue;
        return $this->setCustomAttributes($attributes);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCustomAttributes()
    {
        return $this->_get(self::CUSTOM_ATTRIBUTES);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setCustomAttributes(array $attributes)
    {
        return $this->setData(self::CUSTOM_ATTRIBUTES, $attributes);
    }

    /**
     * Implementation of \IteratorAggregate::getIterator()
     *
     * @return \ArrayIterator
     * @since 2.1.0
     */
    public function getIterator()
    {
        $attributes = (array)$this->getCustomAttributes();
        return new \ArrayIterator($attributes);
    }
}
