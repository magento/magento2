<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Math;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Random data generator
 *
 * @api
 */
class Random
{
    /**#@+
     * Frequently used character classes
     */
    const CHARS_LOWERS = 'abcdefghijklmnopqrstuvwxyz';

    const CHARS_UPPERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    const CHARS_DIGITS = '0123456789';

    /**#@-*/

    /**
     * Get random string.
     *
     * @param int $length
     * @param null|string $chars
     *
     * @return string
     * @throws LocalizedException
     */
    public function getRandomString($length, $chars = null)
    {
        $str = '';
        if (null === $chars) {
            $chars = self::CHARS_LOWERS.self::CHARS_UPPERS.self::CHARS_DIGITS;
        }

        $charsMaxKey = mb_strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[self::getRandomNumber(0, $charsMaxKey)];
        }

        return $str;
    }

    /**
     * Return a random number in the specified range
     *
     * @param $min [optional]
     * @param $max [optional]
     * @return int A random integer value between min (or 0) and max
     * @throws LocalizedException
     */
    public static function getRandomNumber($min = 0, $max = null)
    {
        if (null === $max) {
            $max = mt_getrandmax();
        }
        if ($max < $min) {
            throw new LocalizedException(new Phrase('Invalid range given.'));
        }

        return random_int($min, $max);
    }

    /**
     * Generate a hash from unique ID.
     *
     * @param string $prefix
     * @return string
     */
    public function getUniqueHash($prefix = '')
    {
        return $prefix . $this->getRandomString(32);
    }
}
