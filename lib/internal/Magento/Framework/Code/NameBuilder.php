<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code;

/**
 * Builds namespace with classname out of the parts.
 *
 * @api
 */
class NameBuilder
{
    /**
     * Builds namespace + classname out of the parts array
     *
     * Split every part into pieces by _ and \ and uppercase every piece
     * Then join them back using \
     *
     * @param string[] $parts
     * @return string
     */
    public function buildClassName($parts)
    {
        $separator = '\\';
        $string = join($separator, $parts);
        $string = str_replace('_', $separator, $string);
        $className = ucwords($string, $separator);
        return $className;
    }
}
