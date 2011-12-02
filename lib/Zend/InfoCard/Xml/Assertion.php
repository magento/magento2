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
 * @version    $Id: Assertion.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Zend_InfoCard_Xml_Assertion_Interface
 */
#require_once 'Zend/InfoCard/Xml/Assertion/Interface.php';

/**
 * Factory object to retrieve an Assertion object based on the type of XML document provided
 *
 * @category   Zend
 * @package    Zend_InfoCard
 * @subpackage Zend_InfoCard_Xml
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
final class Zend_InfoCard_Xml_Assertion
{
    /**
     * The namespace for a SAML-formatted Assertion document
     */
    const TYPE_SAML = 'urn:oasis:names:tc:SAML:1.0:assertion';

    /**
     * Constructor (disabled)
     *
     * @return void
     */
    private function __construct()
    {
    }

    /**
     * Returns an instance of a InfoCard Assertion object based on the XML data provided
     *
     * @throws Zend_InfoCard_Xml_Exception
     * @param string $xmlData The XML-Formatted Assertion
     * @return Zend_InfoCard_Xml_Assertion_Interface
     * @throws Zend_InfoCard_Xml_Exception
     */
    static public function getInstance($xmlData)
    {

        if($xmlData instanceof Zend_InfoCard_Xml_Element) {
            $strXmlData = $xmlData->asXML();
        } else if (is_string($xmlData)) {
            $strXmlData = $xmlData;
        } else {
            #require_once 'Zend/InfoCard/Xml/Exception.php';
            throw new Zend_InfoCard_Xml_Exception("Invalid Data provided to create instance");
        }

        $sxe = simplexml_load_string($strXmlData);

        $namespaces = $sxe->getDocNameSpaces();

        foreach($namespaces as $namespace) {
            switch($namespace) {
                case self::TYPE_SAML:
                    include_once 'Zend/InfoCard/Xml/Assertion/Saml.php';
                    return simplexml_load_string($strXmlData, 'Zend_InfoCard_Xml_Assertion_Saml', null);
            }
        }

        #require_once 'Zend/InfoCard/Xml/Exception.php';
        throw new Zend_InfoCard_Xml_Exception("Unable to determine Assertion type by Namespace");
    }
}
