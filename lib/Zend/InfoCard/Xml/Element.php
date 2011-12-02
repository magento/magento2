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
 * @version    $Id: Element.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Zend_InfoCard_Xml_Element_Interface
 */
#require_once 'Zend/InfoCard/Xml/Element/Interface.php';

/**
 * An abstract class representing a an XML data block
 *
 * @category   Zend
 * @package    Zend_InfoCard
 * @subpackage Zend_InfoCard_Xml
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_InfoCard_Xml_Element
    extends SimpleXMLElement
    implements Zend_InfoCard_Xml_Element_Interface
{
    /**
     * Convert the object to a string by displaying its XML content
     *
     * @return string an XML representation of the object
     */
    public function __toString()
    {
        return $this->asXML();
    }

    /**
     * Converts an XML Element object into a DOM object
     *
     * @throws Zend_InfoCard_Xml_Exception
     * @param Zend_InfoCard_Xml_Element $e The object to convert
     * @return DOMElement A DOMElement representation of the same object
     */
    static public function convertToDOM(Zend_InfoCard_Xml_Element $e)
    {
        $dom = dom_import_simplexml($e);

        if(!($dom instanceof DOMElement)) {
            // Zend_InfoCard_Xml_Element exntes SimpleXMLElement, so this should *never* fail
            // @codeCoverageIgnoreStart
            #require_once 'Zend/InfoCard/Xml/Exception.php';
            throw new Zend_InfoCard_Xml_Exception("Failed to convert between SimpleXML and DOM");
            // @codeCoverageIgnoreEnd
        }

        return $dom;
    }

    /**
     * Converts a DOMElement object into the specific class
     *
     * @throws Zend_InfoCard_Xml_Exception
     * @param DOMElement $e The DOMElement object to convert
     * @param string $classname The name of the class to convert it to (must inhert from Zend_InfoCard_Xml_Element)
     * @return Zend_InfoCard_Xml_Element a Xml Element object from the DOM element
     */
    static public function convertToObject(DOMElement $e, $classname)
    {
        if (!class_exists($classname)) {
            #require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($classname);
        }

        $reflection = new ReflectionClass($classname);

        if(!$reflection->isSubclassOf('Zend_InfoCard_Xml_Element')) {
            #require_once 'Zend/InfoCard/Xml/Exception.php';
            throw new Zend_InfoCard_Xml_Exception("DOM element must be converted to an instance of Zend_InfoCard_Xml_Element");
        }

        $sxe = simplexml_import_dom($e, $classname);

        if(!($sxe instanceof Zend_InfoCard_Xml_Element)) {
            // Since we just checked to see if this was a subclass of Zend_infoCard_Xml_Element this shoudl never fail
            // @codeCoverageIgnoreStart
            #require_once 'Zend/InfoCard/Xml/Exception.php';
            throw new Zend_InfoCard_Xml_Exception("Failed to convert between DOM and SimpleXML");
            // @codeCoverageIgnoreEnd
        }

        return $sxe;
    }
}
