<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Filter;

/**
 * Truncate filter
 *
 * Truncate a string to a certain length if necessary, appending the $etc string.
 * $remainder will contain the string that has been replaced with $etc.
 */
class Truncate implements \Zend_Filter_Interface
{
    /**
     * @var int
     */
    protected $length;

    /**
     * @var string
     */
    protected $etc;

    /**
     * @var string
     */
    protected $remainder;

    /**
     * @var bool
     */
    protected $breakWords;

    /**
     * @var \Magento\Framework\Stdlib\String
     */
    protected $string;

    /**
     * @param \Magento\Framework\Stdlib\String $string
     * @param int $length
     * @param string $etc
     * @param string &$remainder
     * @param bool $breakWords
     */
    public function __construct(
        \Magento\Framework\Stdlib\String $string,
        $length = 80,
        $etc = '...',
        &$remainder = '',
        $breakWords = true
    ) {
        $this->string = $string;
        $this->length = $length;
        $this->etc = $etc;
        $this->remainder =& $remainder;
        $this->breakWords = $breakWords;
    }

    /**
     * Filter value
     *
     * @param string $string
     * @return string
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
