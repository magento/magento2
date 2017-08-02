<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Encryption\Helper;

use Zend\Crypt\Utils;

/**
 * Class implements compareString from Zend\Crypt
 *
 * @api
 * @since 2.0.0
 */
class Security
{
    /**
     * Compare two strings in a secure way that avoids string length guessing based on duration of calculation
     *
     * @param  string $expected
     * @param  string $actual
     * @return bool
     * @since 2.0.0
     */
    public static function compareStrings($expected, $actual)
    {
        return Utils::compareStrings($expected, $actual);
    }
}
