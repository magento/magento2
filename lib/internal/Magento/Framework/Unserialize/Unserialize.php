<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
        if (preg_match('/[oc]:[+\-]?\d+:"/i', $string)) {
            trigger_error('String contains serialized object');
            return false;
        }
        return unserialize($string);
    }
}
