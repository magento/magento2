<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Encryption\Helper;

use Zend\Crypt\Utils;

/**
 * Class implements compareString from Zend\Crypt
 */
class Security
{
    /**
     * @param  string $expected
     * @param  string $actual
     * @return bool
     */
    public static function compareStrings($expected, $actual)
    {
        return Utils::compareStrings($expected, $actual);
    }
}
