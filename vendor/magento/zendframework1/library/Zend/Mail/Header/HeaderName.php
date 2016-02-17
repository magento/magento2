<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Mail
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @category   Zend
 * @package    Zend_Mail
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
final class Zend_Mail_Header_HeaderName
{
    /**
     * No public constructor.
     */
    private function __construct()
    {
    }

    /**
     * Filter the header name according to RFC 2822
     *
     * @see    http://www.rfc-base.org/txt/rfc-2822.txt (section 2.2)
     * @param  string $name
     * @return string
     */
    public static function filter($name)
    {
        $result = '';
        $tot    = strlen($name);
        for ($i = 0; $i < $tot; $i += 1) {
            $ord = ord($name[$i]);
            if ($ord > 32 && $ord < 127 && $ord !== 58) {
                $result .= $name[$i];
            }
        }
        return $result;
    }

    /**
     * Determine if the header name contains any invalid characters.
     *
     * @param string $name
     * @return bool
     */
    public static function isValid($name)
    {
        $tot = strlen($name);
        for ($i = 0; $i < $tot; $i += 1) {
            $ord = ord($name[$i]);
            if ($ord < 33 || $ord > 126 || $ord === 58) {
                return false;
            }
        }
        return true;
    }

    /**
     * Assert that the header name is valid.
     *
     * Raises an exception if invalid.
     *
     * @param string $name
     * @throws Exception\RuntimeException
     * @return void
     */
    public static function assertValid($name)
    {
        if (! self::isValid($name)) {
            #require_once 'Zend/Mail/Exception.php';
            throw new Zend_Mail_Exception('Invalid header name detected');
        }
    }
}
