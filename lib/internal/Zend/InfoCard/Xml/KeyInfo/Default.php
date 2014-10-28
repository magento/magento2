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
 * @version    $Id: Default.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Zend_InfoCard_Xml_KeyInfo_Abstract
 */
#require_once 'Zend/InfoCard/Xml/KeyInfo/Abstract.php';

/**
 * Zend_InfoCard_Xml_SecurityTokenReference
 */
#require_once 'Zend/InfoCard/Xml/SecurityTokenReference.php';

/**
 * An object representation of a XML <KeyInfo> block which doesn't provide a namespace
 * In this context, it is assumed to mean that it is the type of KeyInfo block which
 * contains the SecurityTokenReference
 *
 * @category   Zend
 * @package    Zend_InfoCard
 * @subpackage Zend_InfoCard_Xml
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_InfoCard_Xml_KeyInfo_Default extends Zend_InfoCard_Xml_KeyInfo_Abstract
{
    /**
     * Returns the object representation of the SecurityTokenReference block
     *
     * @throws Zend_InfoCard_Xml_Exception
     * @return Zend_InfoCard_Xml_SecurityTokenReference
     */
    public function getSecurityTokenReference()
    {
        $this->registerXPathNamespace('o', 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd');

        list($sectokenref) = $this->xpath('//o:SecurityTokenReference');

        if(!($sectokenref instanceof Zend_InfoCard_Xml_Element)) {
            throw new Zend_InfoCard_Xml_Exception('Could not locate the Security Token Reference');
        }

        return Zend_InfoCard_Xml_SecurityTokenReference::getInstance($sectokenref);
    }
}
