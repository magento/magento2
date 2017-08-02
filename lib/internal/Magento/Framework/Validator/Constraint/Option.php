<?php
/**
 * Constraint option
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator\Constraint;

/**
 * Class \Magento\Framework\Validator\Constraint\Option
 *
 */
class Option implements \Magento\Framework\Validator\Constraint\OptionInterface
{
    /**
     * @var int|string|array
     */
    protected $_value;

    /**
     * Set value
     *
     * @param int|string|array $value
     */
    public function __construct($value)
    {
        $this->_value = $value;
    }

    /**
     * Get value
     *
     * @return int|string|array
     */
    public function getValue()
    {
        return $this->_value;
    }
}
