<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute;

/**
 * @codeCoverageIgnore
 */
class ValidationRule extends \Magento\Framework\Model\AbstractModel implements
    \Magento\Eav\Api\Data\AttributeValidationRuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return $this->getData(self::KEY);
    }

    /**
     * {@inheritdoc}
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
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }
}
