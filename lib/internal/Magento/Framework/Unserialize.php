<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework;

class Unserialize
{
    /**
     * @param string $string
     * @return bool|mixed
     */
    public static function unserialize($string)
    {
        if (preg_match('/o:\d+:"[a-z0-9_]+":\d+:{.*?}/i', $string)) {
            return false;
        }
        return unserialize($string);
    }
}
