<?php
/**
 * Name builder
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code;

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
        $className = str_replace(' ', $separator, ucwords(str_replace($separator, ' ', $string)));
        return $className;
    }
}
