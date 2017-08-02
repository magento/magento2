<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Data;

use Magento\Customer\Api\Data\ValidationRuleInterface;

/**
 * Class \Magento\Customer\Model\Data\ValidationRule
 *
 * @since 2.0.0
 */
class ValidationRule extends \Magento\Framework\Api\AbstractSimpleObject implements
    \Magento\Customer\Api\Data\ValidationRuleInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getValue()
    {
        return $this->_get(self::VALUE);
    }

    /**
     * Set validation rule name
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Set validation rule value
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
