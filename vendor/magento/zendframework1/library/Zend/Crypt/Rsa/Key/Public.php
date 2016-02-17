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
 * @subpackage Rsa
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Crypt_Rsa_Key
 */
#require_once 'Zend/Crypt/Rsa/Key.php';

/**
 * @category   Zend
 * @package    Zend_Crypt
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Crypt_Rsa_Key_Public extends Zend_Crypt_Rsa_Key
{

    protected $_certificateString = null;

    public function __construct($string)
    {
        $this->_parse($string);
    }

    /**
     * @param string $string
     * @throws Zend_Crypt_Exception
     */
    protected function _parse($string)
    {
        if (preg_match("/^-----BEGIN CERTIFICATE-----/", $string)) {
            $this->_certificateString = $string;
        } else {
            $this->_pemString = $string;
        }
        $result = openssl_get_publickey($string);
        if (!$result) {
            /**
             * @see Zend_Crypt_Exception
             */
            #require_once 'Zend/Crypt/Exception.php';
            throw new Zend_Crypt_Exception('Unable to load public key');
        }
        //openssl_pkey_export($result, $public);
        //$this->_pemString = $public;
        $this->_opensslKeyResource = $result;
        $this->_details = openssl_pkey_get_details($this->_opensslKeyResource);
    }

    public function getCertificate()
    {
        return $this->_certificateString;
    }

}
