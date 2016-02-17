<?php

namespace Zend\Filter\Word;

use Zend\Stdlib\StringUtils;

class UnderscoreToStudlyCase extends UnderscoreToCamelCase
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

        $value          = parent::filter($value);
        $lowerCaseFirst = 'lcfirst';

        if (StringUtils::hasPcreUnicodeSupport() && extension_loaded('mbstring')) {
            $lowerCaseFirst = function ($value) {
                if (0 === mb_strlen($value)) {
                    return $value;
                }

                return mb_strtolower(mb_substr($value, 0, 1)) . mb_substr($value, 1);
            };
        }

        return is_array($value) ? array_map($lowerCaseFirst, $value) : call_user_func($lowerCaseFirst, $value);
    }
}
