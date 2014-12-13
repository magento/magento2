<?php
/**
 * Constraint option
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Validator\Constraint;

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
