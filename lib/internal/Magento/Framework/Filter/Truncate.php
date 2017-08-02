<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

/**
 * Truncate filter
 *
 * Truncate a string to a certain length if necessary, appending the $etc string.
 * $remainder will contain the string that has been replaced with $etc.
 * @since 2.0.0
 */
class Truncate implements \Zend_Filter_Interface
{
    /**
     * @var int
     * @since 2.0.0
     */
    protected $length;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $etc;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $remainder;

    /**
     * @var bool
     * @since 2.0.0
     */
    protected $breakWords;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     * @since 2.0.0
     */
    protected $string;

    /**
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param int $length
     * @param string $etc
     * @param string &$remainder
     * @param bool $breakWords
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Stdlib\StringUtils $string,
        $length = 80,
        $etc = '...',
        &$remainder = '',
        $breakWords = true
    ) {
        $this->string = $string;
        $this->length = $length;
        $this->etc = $etc;
        $this->remainder = & $remainder;
        $this->breakWords = $breakWords;
    }

    /**
     * Filter value
     *
     * @param string $string
     * @return string
     * @since 2.0.0
     */
    public function filter($string)
    {
        $length = $this->length;
        $this->remainder = '';
        if (0 == $length) {
            return '';
        }

        $originalLength = $this->string->strlen($string);
        if ($originalLength > $length) {
            $length -= $this->string->strlen($this->etc);
            if ($length <= 0) {
                return '';
            }
            $preparedString = $string;
            $preparedLength = $length;
            if (!$this->breakWords) {
                $preparedString = preg_replace('/\s+?(\S+)?$/u', '', $this->string->substr($string, 0, $length + 1));
                $preparedLength = $this->string->strlen($preparedString);
            }
            $this->remainder = $this->string->substr($string, $preparedLength, $originalLength);
            return $this->string->substr($preparedString, 0, $length) . $this->etc;
        }

        return $string;
    }
}
