<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Filter\Word;

use Zend\Stdlib\StringUtils;

class SeparatorToCamelCase extends AbstractSeparator
{
    /**
     * Defined by Zend\Filter\Filter
     *
     * @param  string|array $value
     * @return string|array
     */
    public function filter($value)
    {
        if (!is_scalar($value) && !is_array($value)) {
            return $value;
        }

        // a unicode safe way of converting characters to \x00\x00 notation
        $pregQuotedSeparator = preg_quote($this->separator, '#');

        if (StringUtils::hasPcreUnicodeSupport()) {
            $patterns = array(
                '#(' . $pregQuotedSeparator.')(\P{Z}{1})#u',
                '#(^\P{Z}{1})#u',
            );
            if (!extension_loaded('mbstring')) {
                $replacements = array(
                    function ($matches) {
                        return strtoupper($matches[2]);
                    },
                    function ($matches) {
                        return strtoupper($matches[1]);
                    },
                );
            } else {
                $replacements = array(
                    function ($matches) {
                        return mb_strtoupper($matches[2], 'UTF-8');
                    },
                    function ($matches) {
                        return mb_strtoupper($matches[1], 'UTF-8');
                    },
                );
            }
        } else {
            $patterns = array(
                '#(' . $pregQuotedSeparator.')([\S]{1})#',
                '#(^[\S]{1})#',
            );
            $replacements = array(
                function ($matches) {
                    return strtoupper($matches[2]);
                },
                function ($matches) {
                    return strtoupper($matches[1]);
                },
            );
        }

        $filtered = $value;
        foreach ($patterns as $index => $pattern) {
            $filtered = preg_replace_callback($pattern, $replacements[$index], $filtered);
        }
        return $filtered;
    }
}
