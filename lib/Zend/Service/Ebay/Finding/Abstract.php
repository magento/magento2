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
 * @package    Zend_Service
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Abstract.php 22791 2010-08-04 16:11:47Z renanbr $
 */

/**
 * @see Zend_Service_Ebay_Abstract
 */
#require_once 'Zend/Service/Ebay/Abstract.php';

/**
 * @see Zend_Service_Ebay_Finding
 */
#require_once 'Zend/Service/Ebay/Finding.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Service_Ebay_Finding_Abstract
{
    /**
     * @var DOMElement
     */
    protected $_dom;

    /**
     * @var DOMXPath
     */
    protected $_xPath;

    /**
     * @var array
     */
    protected $_attributes = array();

    /**
     * @param  DOMElement $dom
     * @return void
     */
    public function __construct(DOMElement $dom)
    {
        $this->_dom = $dom;
        $this->_initXPath();
        $this->_init();
    }

    /**
     * @param  string $tag
     * @param  string $attribute
     * @return mixed
     */
    public function attributes($tag, $attribute = null)
    {
        if (null === $attribute) {
            // all attributes
            if (array_key_exists($tag, $this->_attributes)) {
                return $this->_attributes[$tag];
            }
            return array();
        }

        // a specific attribute
        if (isset($this->_attributes[$tag][$attribute])) {
            return $this->_attributes[$tag][$attribute];
        }
        return null;
    }

    /**
     * Initialize object.
     *
     * Post construct logic, classes must read their members here. Called from
     * {@link __construct()} as final step of object initialization.
     *
     * @return void
     */
    protected function _init()
    {
    }

    /**
     * Load DOMXPath for current DOM object.
     *
     * @see    Zend_Service_Ebay_Finding::_parseResponse()
     * @return void
     */
    protected function _initXPath()
    {
        $document = $this->_dom->ownerDocument;
        if (!isset($document->ebayFindingXPath)) {
            $xpath = new DOMXPath($document);
            foreach (Zend_Service_Ebay_Finding::getXmlNamespaces() as $alias => $uri) {
                $xpath->registerNamespace($alias, $uri);
            }
            $document->ebayFindingXPath = $xpath;
        }
        $this->_xPath = $document->ebayFindingXPath;
    }

    /**
     * @return DOMElement
     */
    public function getDom()
    {
        return $this->_dom;
    }

    /**
     * @return DOMXPath
     */
    public function getXPath()
    {
        return $this->_xPath;
    }

    /**
     * @param  string $path
     * @param  string $type
     * @param  string $array When true means it expects more than one node occurence
     * @return mixed
     */
    protected function _query($path, $type, $array = false)
    {
        // find values
        $values = array();
        $nodes  = $this->_xPath->query($path, $this->_dom);
        foreach ($nodes as $node) {
            $value    = (string) $node->nodeValue;
            $values[] = Zend_Service_Ebay_Abstract::toPhpValue($value, $type);
            if (!$array) {
                break;
            }
        }

        // array
        if ($array) {
            return $values;
        }

        // single value
        if (count($values)) {
            return reset($values);
        }

        // no nodes fount
        return null;
    }
}
