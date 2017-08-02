<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

/**
 * Class \Magento\Framework\Filter\Money
 *
 * @since 2.0.0
 */
class Money implements \Zend_Filter_Interface
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_format;

    /**
     * @param string $format
     * @since 2.0.0
     */
    public function __construct($format)
    {
        $this->_format = $format;
    }

    /**
     * @param float $value
     * @return string
     * @since 2.0.0
     */
    public function filter($value)
    {
        return money_format($this->_format, $value);
    }
}
