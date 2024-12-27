<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

use Laminas\Filter\FilterInterface;

/**
 * @deprecated As money_format() was removed in PHP 8.0
 * @see https://www.php.net/manual/en/function.money-format.php
 */
class Money implements FilterInterface
{
    /**
     * @var string
     */
    protected $_format;

    /**
     * @param string $format
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct($format)
    {
        trigger_error('Class is deprecated', E_USER_DEPRECATED);
    }

    /**
     * Returns the result of filtering $value
     *
     * @param float $value
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function filter($value)
    {
        return '';
    }
}
