<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Random Number Generator
 *
 * PHP versions 4 and 5
 *
 * Here's a short example of how to use this library:
 * <code>
 * <?php
 *    include('Crypt/Random.php');
 *
 *    echo crypt_random();
 * ?>
 * </code>
 *
 * LICENSE: This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston,
 * MA  02111-1307  USA
 *
 * @category   Crypt
 * @package    Crypt_Random
 * @author     Jim Wigginton <terrafrost@php.net>
 * @copyright  MMVII Jim Wigginton
 * @license    http://www.gnu.org/licenses/lgpl.txt
 * @version    $Id: Random.php,v 1.9 2010/04/24 06:40:48 terrafrost Exp $
 * @link       http://phpseclib.sourceforge.net
 */

/**
 * Generate a random value.
 *
 * On 32-bit machines, the largest distance that can exist between $min and $max is 2**31.
 * If $min and $max are farther apart than that then the last ($max - range) numbers.
 *
 * Depending on how this is being used, it may be worth while to write a replacement.  For example,
 * a PHP-based web app that stores its data in an SQL database can collect more entropy than this function
 * can.
 *
 * @param optional Integer $min
 * @param optional Integer $max
 * @return Integer
 * @access public
 */
function crypt_random($min = 0, $max = 0x7FFFFFFF)
{
    if ($min == $max) {
        return $min;
    }

    // see http://en.wikipedia.org/wiki//dev/random
    // if open_basedir is enabled file_exists() will ouput an "open_basedir restriction in effect" warning,
    // so we suppress it.
    if (@file_exists('/dev/urandom')) {
        static $fp;
        if (!$fp) {
            $fp = fopen('/dev/urandom', 'rb');
        }
        extract(unpack('Nrandom', fread($fp, 4)));

        // say $min = 0 and $max = 3.  if we didn't do abs() then we could have stuff like this:
        // -4 % 3 + 0 = -1, even though -1 < $min
        return abs($random) % ($max - $min) + $min;
    }

    /* Prior to PHP 4.2.0, mt_srand() had to be called before mt_rand() could be called.
       Prior to PHP 5.2.6, mt_rand()'s automatic seeding was subpar, as elaborated here:

       http://www.suspekt.org/2008/08/17/mt_srand-and-not-so-random-numbers/

       The seeding routine is pretty much ripped from PHP's own internal GENERATE_SEED() macro:

       http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3_2/ext/standard/php_rand.h?view=markup */
    if (version_compare(PHP_VERSION, '5.2.5', '<=')) { 
        static $seeded;
        if (!isset($seeded)) {
            $seeded = true;
            mt_srand(fmod(time() * getmypid(), 0x7FFFFFFF) ^ fmod(1000000 * lcg_value(), 0x7FFFFFFF));
        }
    }

    static $crypto;

    // The CSPRNG's Yarrow and Fortuna periodically reseed.  This function can be reseeded by hitting F5
    // in the browser and reloading the page.

    if (!isset($crypto)) {
        $key = $iv = '';
        for ($i = 0; $i < 8; $i++) {
            $key.= pack('n', mt_rand(0, 0xFFFF));
            $iv .= pack('n', mt_rand(0, 0xFFFF));
        }
        switch (true) {
            case class_exists('Crypt_AES'):
                $crypto = new Crypt_AES(CRYPT_AES_MODE_CTR);
                break;
            case class_exists('Crypt_TripleDES'):
                $crypto = new Crypt_TripleDES(CRYPT_DES_MODE_CTR);
                break;
            case class_exists('Crypt_DES'):
                $crypto = new Crypt_DES(CRYPT_DES_MODE_CTR);
                break;
            case class_exists('Crypt_RC4'):
                $crypto = new Crypt_RC4();
                break;
            default:
                extract(unpack('Nrandom', pack('H*', sha1(mt_rand(0, 0x7FFFFFFF)))));
                return abs($random) % ($max - $min) + $min;
        }
        $crypto->setKey($key);
        $crypto->setIV($iv);
        $crypto->enableContinuousBuffer();
    }

    extract(unpack('Nrandom', $crypto->encrypt("\0\0\0\0")));
    return abs($random) % ($max - $min) + $min;
}
