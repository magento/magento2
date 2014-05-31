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
 * @package    Zend_Service_Amazon
 * @subpackage Authentication
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * @see Zend_Service_Amazon_Authentication
 */
#require_once 'Zend/Service/Amazon/Authentication.php';

/**
 * @see Zend_Crypt_Hmac
 */
#require_once 'Zend/Crypt/Hmac.php';

/**
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage Authentication
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Amazon_Authentication_S3 extends Zend_Service_Amazon_Authentication
{
    /**
     * Add the S3 Authorization signature to the request headers
     *
     * @param  string $method
     * @param  string $path
     * @param  array &$headers
     * @return string
     */
    public function generateSignature($method, $path, &$headers)
    {
        if (! is_array($headers)) {
            $headers = array($headers);
        }
        
        $type = $md5 = $date = '';
        
        // Search for the Content-type, Content-MD5 and Date headers
        foreach ($headers as $key => $val) {
            if (strcasecmp($key, 'content-type') == 0) {
                $type = $val;
            } else if (strcasecmp($key, 'content-md5') == 0) {
                $md5 = $val;
            } else if (strcasecmp($key, 'date') == 0) {
                $date = $val;
            }
        }
        
        // If we have an x-amz-date header, use that instead of the normal Date
        if (isset($headers['x-amz-date']) && isset($date)) {
            $date = '';
        }
        
        $sig_str = "$method\n$md5\n$type\n$date\n";

        // For x-amz- headers, combine like keys, lowercase them, sort them
        // alphabetically and remove excess spaces around values
        $amz_headers = array();
        foreach ($headers as $key => $val) {
            $key = strtolower($key);
            if (substr($key, 0, 6) == 'x-amz-') {
                if (is_array($val)) {
                    $amz_headers[$key] = $val;
                } else {
                    $amz_headers[$key][] = preg_replace('/\s+/', ' ', $val);
                }
            }
        }
        if (!empty($amz_headers)) {
            ksort($amz_headers);
            foreach ($amz_headers as $key => $val) {
                $sig_str .= $key . ':' . implode(',', $val) . "\n";
            }
        }
        
        $sig_str .= '/'.parse_url($path, PHP_URL_PATH);
        if (strpos($path, '?location') !== false) {
            $sig_str .= '?location';
        } else 
            if (strpos($path, '?acl') !== false) {
                $sig_str .= '?acl';
            } else 
                if (strpos($path, '?torrent') !== false) {
                    $sig_str .= '?torrent';
                }
        
        $signature = base64_encode(Zend_Crypt_Hmac::compute($this->_secretKey, 'sha1', utf8_encode($sig_str), Zend_Crypt_Hmac::BINARY));
        $headers['Authorization'] = 'AWS ' . $this->_accessKey . ':' . $signature;

        return $sig_str;
    }
}
