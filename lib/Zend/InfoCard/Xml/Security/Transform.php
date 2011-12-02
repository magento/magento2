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
 * @subpackage Zend_InfoCard_Xml_Security
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Transform.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * A class to create a transform rule set based on XML URIs and then apply those rules
 * in the correct order to a given XML input
 *
 * @category   Zend
 * @package    Zend_InfoCard
 * @subpackage Zend_InfoCard_Xml_Security
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_InfoCard_Xml_Security_Transform
{
    /**
     * A list of transforms to apply
     *
     * @var array
     */
    protected $_transformList = array();

    /**
     * Returns the name of the transform class based on a given URI
     *
     * @throws Zend_InfoCard_Xml_Security_Exception
     * @param string $uri The transform URI
     * @return string The transform implementation class name
     */
    protected function _findClassbyURI($uri)
    {
        switch($uri) {
            case 'http://www.w3.org/2000/09/xmldsig#enveloped-signature':
                return 'Zend_InfoCard_Xml_Security_Transform_EnvelopedSignature';
            case 'http://www.w3.org/2001/10/xml-exc-c14n#':
                return 'Zend_InfoCard_Xml_Security_Transform_XmlExcC14N';
            default:
                #require_once 'Zend/InfoCard/Xml/Security/Exception.php';
                throw new Zend_InfoCard_Xml_Security_Exception("Unknown or Unsupported Transformation Requested");
        }
    }

    /**
     * Add a Transform URI to the list of transforms to perform
     *
     * @param string $uri The Transform URI
     * @return Zend_InfoCard_Xml_Security_Transform
     */
    public function addTransform($uri)
    {
        $class = $this->_findClassbyURI($uri);

        $this->_transformList[] = array('uri' => $uri,
                                        'class' => $class);
        return $this;
    }

    /**
     * Return the list of transforms to perform
     *
     * @return array The list of transforms
     */
    public function getTransformList()
    {
        return $this->_transformList;
    }

    /**
     * Apply the transforms in the transform list to the input XML document
     *
     * @param string $strXmlDocument The input XML
     * @return string The XML after the transformations have been applied
     */
    public function applyTransforms($strXmlDocument)
    {
        foreach($this->_transformList as $transform) {
            if (!class_exists($transform['class'])) {
                #require_once 'Zend/Loader.php';
                Zend_Loader::loadClass($transform['class']);
            }

            $transformer = new $transform['class'];

            // We can't really test this check because it would require logic changes in the component itself
            // @codeCoverageIgnoreStart
            if(!($transformer instanceof Zend_InfoCard_Xml_Security_Transform_Interface)) {
                #require_once 'Zend/InfoCard/Xml/Security/Exception.php';
                throw new Zend_InfoCard_Xml_Security_Exception("Transforms must implement the Transform Interface");
            }
            // @codeCoverageIgnoreEnd

            $strXmlDocument = $transformer->transform($strXmlDocument);
        }

        return $strXmlDocument;
    }
}
