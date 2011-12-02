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
 * @version    $Id: Interface.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Zend_InfoCard_Cipher_PKI_Adapter_Abstract
 */
#require_once 'Zend/InfoCard/Cipher/Pki/Adapter/Abstract.php';

/**
 * The interface which defines the RSA Public-key encryption object
 *
 * @category   Zend
 * @package    Zend_InfoCard
 * @subpackage Zend_InfoCard_Cipher
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_InfoCard_Cipher_Pki_Rsa_Interface
{
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
    public function decrypt($encryptedData, $privateKey, $password = null, $padding = Zend_InfoCard_Cipher_Pki_Adapter_Abstract::NO_PADDING);
}
