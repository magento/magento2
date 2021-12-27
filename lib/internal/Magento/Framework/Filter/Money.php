<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

/**
 * @deprecated As money_format() was removed in PHP 8.0
 * @see https://www.php.net/manual/en/function.money-format.php
 */
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
     * Returns the result of filtering $value
     *
     * @param float $value
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated
     */
    public function filter($value)
    {
        trigger_error('Class is deprecated', E_USER_DEPRECATED);
        return '';
    }
}
