<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class ValidationRule extends \Magento\Framework\Model\AbstractModel implements
    \Magento\Eav\Api\Data\AttributeValidationRuleInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getKey()
    {
        return $this->getData(self::KEY);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    /**
     * Set object key
     *
     * @param string $key
     * @return $this
     * @since 2.0.0
     */
    public function setKey($key)
    {
        return $this->setData(self::KEY, $key);
    }

    /**
     * Set object value
     *
     * @param string $value
     * @return $this
     * @since 2.0.0
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }
}
