<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Config;

class TestServiceForClassReflector
{
    /**
     * Basic random string generator. This line is short description
     * This line is long description.
     *
     * This is still the long description.
     *
     * @param int $length length of the random string
     * @return string random string
     */
    public function generateRandomString($length)
    {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }
}
