<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

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
