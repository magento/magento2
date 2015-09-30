<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Unserialize;

class Unserialize
{
    /**
     * @param string $string
     * @return bool|mixed
     */
    public function unserialize($string)
    {
        if (preg_match('/o:\d+:"[a-z0-9_]+":\d+:{.*?}/i', $string)) {
            trigger_error('String contains serialized object');
            return false;
        }
        return unserialize($string);
    }
}
