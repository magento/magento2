<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Filter;

use Zend\Stdlib\StringUtils;

class Digits extends AbstractFilter
{
    /**
     * Defined by Zend\Filter\FilterInterface
     *
     * Returns the string $value, removing all but digit characters
     *
     * If the value provided is not integer, float or string, the value will remain unfiltered
     *
     * @param  string $value
     * @return string|mixed
     */
    public function filter($value)
    {
        if (is_int($value)) {
            return (string) $value;
        }
        if (! (is_float($value) || is_string($value))) {
            return $value;
        }
        $value = (string) $value;

        if (!StringUtils::hasPcreUnicodeSupport()) {
            // POSIX named classes are not supported, use alternative 0-9 match
            $pattern = '/[^0-9]/';
        } elseif (extension_loaded('mbstring')) {
            // Filter for the value with mbstring
            $pattern = '/[^[:digit:]]/';
        } else {
            // Filter for the value without mbstring
            $pattern = '/[\p{^N}]/';
        }

        return preg_replace($pattern, '', $value);
    }
}
