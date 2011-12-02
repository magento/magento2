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
 * @package    Zend_InfoCard
 * @subpackage Zend_InfoCard_Cipher
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Rsa.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Zend_InfoCard_Cipher_Pki_Adapter_Abstract
 */
#require_once 'Zend/InfoCard/Cipher/Pki/Adapter/Abstract.php';

/**
 * Zend_InfoCard_Cipher_Pki_Rsa_Interface
 */
#require_once 'Zend/InfoCard/Cipher/Pki/Rsa/Interface.php';

/**
 * RSA Public Key Encryption Cipher Object for the InfoCard component. Relies on OpenSSL
 * to implement the RSA algorithm
 *
 * @category   Zend
 * @package    Zend_InfoCard
 * @subpackage Zend_InfoCard_Cipher
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_InfoCard_Cipher_Pki_Adapter_Rsa
    extends Zend_InfoCard_Cipher_Pki_Adapter_Abstract
    implements Zend_InfoCard_Cipher_Pki_Rsa_Interface
{

    /**
     * Object Constructor
     *
     * @param integer $padding The type of Padding to use
     */
    public function __construct($padding = Zend_InfoCard_Cipher_Pki_Adapter_Abstract::NO_PADDING)
    {
        // Can't test this..
        // @codeCoverageIgnoreStart
        if(!extension_loaded('openssl')) {
            #require_once 'Zend/InfoCard/Cipher/Exception.php';
            throw new Zend_InfoCard_Cipher_Exception("Use of this PKI RSA Adapter requires the openssl extension loaded");
        }
        // @codeCoverageIgnoreEnd

        $this->setPadding($padding);
    }

    /**
     * Decrypts RSA encrypted data using the given private key
     *
     * @throws Zend_InfoCard_Cipher_Exception
     * @param string $encryptedData The encrypted data in binary format
     * @param string $privateKey The private key in binary format
     * @param string $password The private key passphrase
     * @param integer $padding The padding to use during decryption (of not provided object value will be used)
     * @return string The decrypted data
     */
    public function decrypt($encryptedData, $privateKey, $password = null, $padding = null)
    {
        $private_key = openssl_pkey_get_private(array($privateKey, $password));

        if(!$private_key) {
            #require_once 'Zend/InfoCard/Cipher/Exception.php';
            throw new Zend_InfoCard_Cipher_Exception("Failed to load private key");
        }

        if($padding !== null) {
            try {
                $this->setPadding($padding);
            } catch(Exception $e) {
                openssl_free_key($private_key);
                throw $e;
            }
        }

        switch($this->getPadding()) {
            case self::NO_PADDING:
                $openssl_padding = OPENSSL_NO_PADDING;
                break;
            case self::OAEP_PADDING:
                $openssl_padding = OPENSSL_PKCS1_OAEP_PADDING;
                break;
        }

        $result = openssl_private_decrypt($encryptedData, $decryptedData, $private_key, $openssl_padding);

        openssl_free_key($private_key);

        if(!$result) {
            #require_once 'Zend/InfoCard/Cipher/Exception.php';
            throw new Zend_InfoCard_Cipher_Exception("Unable to Decrypt Value using provided private key");
        }

        if($this->getPadding() == self::NO_PADDING) {
            $decryptedData = substr($decryptedData, 2);
            $start = strpos($decryptedData, 0) + 1;
            $decryptedData = substr($decryptedData, $start);
        }

        return $decryptedData;
    }
}
