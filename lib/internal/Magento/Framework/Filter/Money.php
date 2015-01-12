<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

class Money implements \Zend_Filter_Interface
{
    /**
     * @var string
     */
    protected $_format;

    /**
     * @param string $format
     */
    public function __construct($format)
    {
        $this->_format = $format;
    }

    /**
     * @param float $value
     * @return string
     */
    public function filter($value)
    {
        return money_format($this->_format, $value);
    }
}
