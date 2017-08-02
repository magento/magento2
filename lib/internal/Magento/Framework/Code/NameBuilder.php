<?php
/**
 * Name builder
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code;

/**
 * Class \Magento\Framework\Code\NameBuilder
 *
 * @since 2.0.0
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
     * @since 2.0.0
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
