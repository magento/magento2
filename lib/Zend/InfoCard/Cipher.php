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
 * @version    $Id: Cipher.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Provides an abstraction for encryption ciphers used in an Information Card
 * implementation
 *
 * @category   Zend
 * @package    Zend_InfoCard
 * @subpackage Zend_InfoCard_Cipher
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_InfoCard_Cipher
{
    /**
     * AES 256 Encryption with CBC
     */
    const ENC_AES256CBC      = 'http://www.w3.org/2001/04/xmlenc#aes256-cbc';

    /**
     * AES 128 Encryption with CBC
     */
    const ENC_AES128CBC      = 'http://www.w3.org/2001/04/xmlenc#aes128-cbc';

    /**
     * RSA Public Key Encryption with OAEP Padding
     */
    const ENC_RSA_OAEP_MGF1P = 'http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p';

    /**
     * RSA Public Key Encryption with no padding
     */
    const ENC_RSA            = 'http://www.w3.org/2001/04/xmlenc#rsa-1_5';

    /**
     * Constructor (disabled)
     *
     * @return void
     * @codeCoverageIgnoreStart
     */
    protected function __construct()
    {
    }
    // @codeCoverageIgnoreEnd
    /**
     * Returns an instance of a cipher object supported based on the URI provided
     *
     * @throws Zend_InfoCard_Cipher_Exception
     * @param string $uri The URI of the encryption method wantde
     * @return mixed an Instance of Zend_InfoCard_Cipher_Symmetric_Interface or Zend_InfoCard_Cipher_Pki_Interface
     *               depending on URI
     */
    static public function getInstanceByURI($uri)
    {
        switch($uri) {
            case self::ENC_AES256CBC:
                include_once 'Zend/InfoCard/Cipher/Symmetric/Adapter/Aes256cbc.php';
                return new Zend_InfoCard_Cipher_Symmetric_Adapter_Aes256cbc();

            case self::ENC_AES128CBC:
                include_once 'Zend/InfoCard/Cipher/Symmetric/Adapter/Aes128cbc.php';
                return new Zend_InfoCard_Cipher_Symmetric_Adapter_Aes128cbc();

            case self::ENC_RSA_OAEP_MGF1P:
                include_once 'Zend/InfoCard/Cipher/Pki/Adapter/Rsa.php';
                return new Zend_InfoCard_Cipher_Pki_Adapter_Rsa(Zend_InfoCard_Cipher_Pki_Adapter_Rsa::OAEP_PADDING);
                break;

            case self::ENC_RSA:
                include_once 'Zend/InfoCard/Cipher/Pki/Adapter/Rsa.php';
                return new Zend_InfoCard_Cipher_Pki_Adapter_Rsa(Zend_InfoCard_Cipher_Pki_Adapter_Rsa::NO_PADDING);
                break;

            default:
                #require_once 'Zend/InfoCard/Cipher/Exception.php';
                throw new Zend_InfoCard_Cipher_Exception("Unknown Cipher URI");
        }
    }
}
