<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Http\Header;

final class HeaderValue
{
    /**
     * Private constructor; non-instantiable.
     */
    private function __construct()
    {
    }

    /**
     * Filter a header value
     *
     * Ensures CRLF header injection vectors are filtered.
     *
     * Per RFC 7230, only VISIBLE ASCII characters, spaces, and horizontal
     * tabs are allowed in values; only one whitespace character is allowed
     * between visible characters.
     *
     * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
     * @param string $value
     * @return string
     */
    public static function filter($value)
    {
        $value  = (string) $value;
        $length = strlen($value);
        $string = '';
        for ($i = 0; $i < $length; $i += 1) {
            $ascii = ord($value[$i]);

            // Non-visible, non-whitespace characters
            // 9 === horizontal tab
            // 32-126, 128-254 === visible
            // 127 === DEL
            // 255 === null byte
            if (($ascii < 32 && $ascii !== 9)
                || $ascii === 127
                || $ascii > 254
            ) {
                continue;
            }

            $string .= $value[$i];
        }

        return $string;
    }

    /**
     * Validate a header value.
     *
     * Per RFC 7230, only VISIBLE ASCII characters, spaces, and horizontal
     * tabs are allowed in values; only one whitespace character is allowed
     * between visible characters.
     *
     * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
     * @param string $value
     * @return bool
     */
    public static function isValid($value)
    {
        $value  = (string) $value;
        $length = strlen($value);
        for ($i = 0; $i < $length; $i += 1) {
            $ascii = ord($value[$i]);

            // Non-visible, non-whitespace characters
            // 9 === horizontal tab
            // 32-126, 128-254 === visible
            // 127 === DEL
            // 255 === null byte
            if (($ascii < 32 && $ascii !== 9)
                || $ascii === 127
                || $ascii > 254
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Assert a header value is valid.
     *
     * @param string $value
     * @throws Exception\RuntimeException for invalid values
     * @return void
     */
    public static function assertValid($value)
    {
        if (! self::isValid($value)) {
            throw new Exception\InvalidArgumentException('Invalid header value');
        }
    }
}
