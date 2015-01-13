<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Data;

class ValidationRule extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Magento\Customer\Api\Data\ValidationRuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->_get(self::VALUE);
    }
}
