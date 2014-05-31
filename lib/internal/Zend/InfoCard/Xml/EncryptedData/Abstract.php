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
 * @subpackage Zend_InfoCard_Xml
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Abstract.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Zend_InfoCard_Xml_Element
 */
#require_once 'Zend/InfoCard/Xml/Element.php';

/**
 * Zend_InfoCard_Xml_KeyInfo
 */
#require_once 'Zend/InfoCard/Xml/KeyInfo.php';

/**
 * An abstract class representing a generic EncryptedData XML block. This class is extended
 * into a specific type of EncryptedData XML block (i.e. XmlEnc) as necessary
 *
 * @category   Zend
 * @package    Zend_InfoCard
 * @subpackage Zend_InfoCard_Xml
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_InfoCard_Xml_EncryptedData_Abstract extends Zend_InfoCard_Xml_Element
{

    /**
     * Returns the KeyInfo Block
     *
     * @return Zend_InfoCard_Xml_KeyInfo_Abstract
     */
    public function getKeyInfo()
    {
        return Zend_InfoCard_Xml_KeyInfo::getInstance($this->KeyInfo[0]);
    }

    /**
     * Return the Encryption method used to encrypt the assertion document
     * (the symmetric cipher)
     *
     * @throws Zend_InfoCard_Xml_Exception
     * @return string The URI of the Symmetric Encryption Method used
     */
    public function getEncryptionMethod()
    {

        /**
         * @todo This is pretty hacky unless we can always be confident that the first
         * EncryptionMethod block is the correct one (the AES or compariable symetric algorithm)..
         * the second is the PK method if provided.
         */
        list($encryption_method) = $this->xpath("//enc:EncryptionMethod");

        if(!($encryption_method instanceof Zend_InfoCard_Xml_Element)) {
            throw new Zend_InfoCard_Xml_Exception("Unable to find the enc:EncryptionMethod symmetric encryption block");
        }

        $dom = self::convertToDOM($encryption_method);

        if(!$dom->hasAttribute('Algorithm')) {
            throw new Zend_InfoCard_Xml_Exception("Unable to determine the encryption algorithm in the Symmetric enc:EncryptionMethod XML block");
        }

        return $dom->getAttribute('Algorithm');
    }

    /**
     * Returns the value of the encrypted block
     *
     * @return string the value of the encrypted CipherValue block
     */
    abstract function getCipherValue();
}
