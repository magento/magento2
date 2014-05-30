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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: XmlDSig.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Zend_InfoCard_Xml_KeyInfo_Abstract
 */
#require_once 'Zend/InfoCard/Xml/KeyInfo/Abstract.php';

/**
 * Zend_InfoCard_Xml_EncryptedKey
 */
#require_once 'Zend/InfoCard/Xml/EncryptedKey.php';

/**
 * Zend_InfoCard_Xml_KeyInfo_Interface
 */
#require_once 'Zend/InfoCard/Xml/KeyInfo/Interface.php';

/**
 * Represents a Xml Digital Signature XML Data Block
 *
 * @category   Zend
 * @package    Zend_InfoCard
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_InfoCard_Xml_KeyInfo_XmlDSig
    extends Zend_InfoCard_Xml_KeyInfo_Abstract
    implements Zend_InfoCard_Xml_KeyInfo_Interface
{
    /**
     * Returns an instance of the EncryptedKey Data Block
     *
     * @throws Zend_InfoCard_Xml_Exception
     * @return Zend_InfoCard_Xml_EncryptedKey
     */
    public function getEncryptedKey()
    {
        $this->registerXPathNamespace('e', 'http://www.w3.org/2001/04/xmlenc#');
        list($encryptedkey) = $this->xpath('//e:EncryptedKey');

        if(!($encryptedkey instanceof Zend_InfoCard_Xml_Element)) {
            throw new Zend_InfoCard_Xml_Exception("Failed to retrieve encrypted key");
        }

        return Zend_InfoCard_Xml_EncryptedKey::getInstance($encryptedkey);
    }

    /**
     * Returns the KeyInfo Block within the encrypted key
     *
     * @return Zend_InfoCard_Xml_KeyInfo_Default
     */
    public function getKeyInfo()
    {
        return $this->getEncryptedKey()->getKeyInfo();
    }
}
