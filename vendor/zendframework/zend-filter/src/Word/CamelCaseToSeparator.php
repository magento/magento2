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

class CamelCaseToSeparator extends AbstractSeparator
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

        if (StringUtils::hasPcreUnicodeSupport()) {
            $pattern     = array('#(?<=(?:\p{Lu}))(\p{Lu}\p{Ll})#', '#(?<=(?:\p{Ll}|\p{Nd}))(\p{Lu})#');
            $replacement = array($this->separator . '\1', $this->separator . '\1');
        } else {
            $pattern     = array('#(?<=(?:[A-Z]))([A-Z]+)([A-Z][a-z])#', '#(?<=(?:[a-z0-9]))([A-Z])#');
            $replacement = array('\1' . $this->separator . '\2', $this->separator . '\1');
        }

        return preg_replace($pattern, $replacement, $value);
    }
}
