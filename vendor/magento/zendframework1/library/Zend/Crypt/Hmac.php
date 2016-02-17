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
 * @package    Zend_Crypt
 * @subpackage Hmac
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Crypt
 */
#require_once 'Zend/Crypt.php';

/**
 * PHP implementation of the RFC 2104 Hash based Message Authentication Code
 * algorithm.
 *
 * @todo  Patch for refactoring failed tests (key block sizes >80 using internal algo)
 * @todo       Check if mhash() is a required alternative (will be PECL-only soon)
 * @category   Zend
 * @package    Zend_Crypt
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Crypt_Hmac extends Zend_Crypt
{

    /**
     * The key to use for the hash
     *
     * @var string
     */
    protected static $_key = null;

    /**
     * pack() format to be used for current hashing method
     *
     * @var string
     */
    protected static $_packFormat = null;

    /**
     * Hashing algorithm; can be the md5/sha1 functions or any algorithm name
     * listed in the output of PHP 5.1.2+ hash_algos().
     *
     * @var string
     */
    protected static $_hashAlgorithm = 'md5';

    /**
     * List of algorithms supported my mhash()
     *
     * @var array
     */
    protected static $_supportedMhashAlgorithms = array('adler32',' crc32', 'crc32b', 'gost',
            'haval128', 'haval160', 'haval192', 'haval256', 'md4', 'md5', 'ripemd160',
            'sha1', 'sha256', 'tiger', 'tiger128', 'tiger160');

    /**
     * Constants representing the output mode of the hash algorithm
     */
    const STRING = 'string';
    const BINARY = 'binary';

    /**
     * Performs a HMAC computation given relevant details such as Key, Hashing
     * algorithm, the data to compute MAC of, and an output format of String,
     * Binary notation or BTWOC.
     *
     * @param string $key
     * @param string $hash
     * @param string $data
     * @param string $output
     * @throws Zend_Crypt_Hmac_Exception
     * @return string
     */
    public static function compute($key, $hash, $data, $output = self::STRING)
    {
        // set the key
        if (!isset($key) || empty($key)) {
            #require_once 'Zend/Crypt/Hmac/Exception.php';
            throw new Zend_Crypt_Hmac_Exception('provided key is null or empty');
        }
        self::$_key = $key;

        // set the hash
        self::_setHashAlgorithm($hash);

        // perform hashing and return
        return self::_hash($data, $output);
    }

    /**
     * Setter for the hash method.
     *
     * @param string $hash
     * @throws Zend_Crypt_Hmac_Exception
     * @return Zend_Crypt_Hmac
     */
    protected static function _setHashAlgorithm($hash)
    {
        if (!isset($hash) || empty($hash)) {
            #require_once 'Zend/Crypt/Hmac/Exception.php';
            throw new Zend_Crypt_Hmac_Exception('provided hash string is null or empty');
        }

        $hash = strtolower($hash);
        $hashSupported = false;

        if (function_exists('hash_algos') && in_array($hash, hash_algos())) {
            $hashSupported = true;
        }

        if ($hashSupported === false && function_exists('mhash') && in_array($hash, self::$_supportedAlgosMhash)) {
            $hashSupported = true;
        }

        if ($hashSupported === false) {
            #require_once 'Zend/Crypt/Hmac/Exception.php';
            throw new Zend_Crypt_Hmac_Exception('hash algorithm provided is not supported on this PHP installation; please enable the hash or mhash extensions');
        }
        self::$_hashAlgorithm = $hash;
    }

    /**
     * Perform HMAC and return the keyed data
     *
     * @param string $data
     * @param string $output
     * @param bool $internal Option to not use hash() functions for testing
     * @return string
     */
    protected static function _hash($data, $output = self::STRING, $internal = false)
    {
        if (function_exists('hash_hmac')) {
            if ($output == self::BINARY) {
                return hash_hmac(self::$_hashAlgorithm, $data, self::$_key, true);
            }
            return hash_hmac(self::$_hashAlgorithm, $data, self::$_key);
        }

        if (function_exists('mhash')) {
            if ($output == self::BINARY) {
                return mhash(self::_getMhashDefinition(self::$_hashAlgorithm), $data, self::$_key);
            }
            $bin = mhash(self::_getMhashDefinition(self::$_hashAlgorithm), $data, self::$_key);
            return bin2hex($bin);
        }
    }

    /**
     * Since MHASH accepts an integer constant representing the hash algorithm
     * we need to make a small detour to get the correct integer matching our
     * algorithm's name.
     *
     * @param string $hashAlgorithm
     * @return integer
     */
    protected static function _getMhashDefinition($hashAlgorithm)
    {
        for ($i = 0; $i <= mhash_count(); $i++)
        {
            $types[mhash_get_hash_name($i)] = $i;
        }
        return $types[strtoupper($hashAlgorithm)];
    }

}
