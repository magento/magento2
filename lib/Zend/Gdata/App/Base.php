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
 * @package    Zend_Gdata
 * @subpackage App
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Base.php 22662 2010-07-24 17:37:36Z mabe $
 */

/**
 * @see Zend_Gdata_App_Util
 */
#require_once 'Zend/Gdata/App/Util.php';

/**
 * Abstract class for all XML elements
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage App
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Gdata_App_Base
{

    /**
     * @var string The XML element name, including prefix if desired
     */
    protected $_rootElement = null;

    /**
     * @var string The XML namespace prefix
     */
    protected $_rootNamespace = 'atom';

    /**
     * @var string The XML namespace URI - takes precedence over lookup up the
     * corresponding URI for $_rootNamespace
     */
    protected $_rootNamespaceURI = null;

    /**
     * @var array Leftover elements which were not handled
     */
    protected $_extensionElements = array();

    /**
     * @var array Leftover attributes which were not handled
     */
    protected $_extensionAttributes = array();

    /**
     * @var string XML child text node content
     */
    protected $_text = null;

    /**
     * @var array Memoized results from calls to lookupNamespace() to avoid
     *      expensive calls to getGreatestBoundedValue(). The key is in the
     *      form 'prefix-majorVersion-minorVersion', and the value is the
     *      output from getGreatestBoundedValue().
     */
    protected static $_namespaceLookupCache = array();

    /**
     * List of namespaces, as a three-dimensional array. The first dimension
     * represents the namespace prefix, the second dimension represents the
     * minimum major protocol version, and the third dimension is the minimum
     * minor protocol version. Null keys are NOT allowed.
     *
     * When looking up a namespace for a given prefix, the greatest version
     * number (both major and minor) which is less than the effective version
     * should be used.
     *
     * @see lookupNamespace()
     * @see registerNamespace()
     * @see registerAllNamespaces()
     * @var array
     */
   protected $_namespaces = array(
        'atom'      => array(
            1 => array(
                0 => 'http://www.w3.org/2005/Atom'
                )
            ),
        'app'       => array(
            1 => array(
                0 => 'http://purl.org/atom/app#'
                ),
            2 => array(
                0 => 'http://www.w3.org/2007/app'
                )
            )
        );

    public function __construct()
    {
    }

    /**
     * Returns the child text node of this element
     * This represents any raw text contained within the XML element
     *
     * @return string Child text node
     */
    public function getText($trim = true)
    {
        if ($trim) {
            return trim($this->_text);
        } else {
            return $this->_text;
        }
    }

    /**
     * Sets the child text node of this element
     * This represents any raw text contained within the XML element
     *
     * @param string $value Child text node
     * @return Zend_Gdata_App_Base Returns an object of the same type as 'this' to provide a fluent interface.
     */
    public function setText($value)
    {
        $this->_text = $value;
        return $this;
    }

    /**
     * Returns an array of all elements not matched to data model classes
     * during the parsing of the XML
     *
     * @return array All elements not matched to data model classes during parsing
     */
    public function getExtensionElements()
    {
        return $this->_extensionElements;
    }

    /**
     * Sets an array of all elements not matched to data model classes
     * during the parsing of the XML.  This method can be used to add arbitrary
     * child XML elements to any data model class.
     *
     * @param array $value All extension elements
     * @return Zend_Gdata_App_Base Returns an object of the same type as 'this' to provide a fluent interface.
     */
    public function setExtensionElements($value)
    {
        $this->_extensionElements = $value;
        return $this;
    }

    /**
     * Returns an array of all extension attributes not transformed into data
     * model properties during parsing of the XML.  Each element of the array
     * is a hashed array of the format:
     *     array('namespaceUri' => string, 'name' => string, 'value' => string);
     *
     * @return array All extension attributes
     */
    public function getExtensionAttributes()
    {
        return $this->_extensionAttributes;
    }

    /**
     * Sets an array of all extension attributes not transformed into data
     * model properties during parsing of the XML.  Each element of the array
     * is a hashed array of the format:
     *     array('namespaceUri' => string, 'name' => string, 'value' => string);
     * This can be used to add arbitrary attributes to any data model element
     *
     * @param array $value All extension attributes
     * @return Zend_Gdata_App_Base Returns an object of the same type as 'this' to provide a fluent interface.
     */
    public function setExtensionAttributes($value)
    {
        $this->_extensionAttributes = $value;
        return $this;
    }

    /**
     * Retrieves a DOMElement which corresponds to this element and all
     * child properties.  This is used to build an entry back into a DOM
     * and eventually XML text for sending to the server upon updates, or
     * for application storage/persistence.
     *
     * @param DOMDocument $doc The DOMDocument used to construct DOMElements
     * @return DOMElement The DOMElement representing this element and all
     * child properties.
     */
    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        if ($doc === null) {
            $doc = new DOMDocument('1.0', 'utf-8');
        }
        if ($this->_rootNamespaceURI != null) {
            $element = $doc->createElementNS($this->_rootNamespaceURI, $this->_rootElement);
        } elseif ($this->_rootNamespace !== null) {
            if (strpos($this->_rootElement, ':') === false) {
                $elementName = $this->_rootNamespace . ':' . $this->_rootElement;
            } else {
                $elementName = $this->_rootElement;
            }
            $element = $doc->createElementNS($this->lookupNamespace($this->_rootNamespace), $elementName);
        } else {
            $element = $doc->createElement($this->_rootElement);
        }
        if ($this->_text != null) {
            $element->appendChild($element->ownerDocument->createTextNode($this->_text));
        }
        foreach ($this->_extensionElements as $extensionElement) {
            $element->appendChild($extensionElement->getDOM($element->ownerDocument));
        }
        foreach ($this->_extensionAttributes as $attribute) {
            $element->setAttribute($attribute['name'], $attribute['value']);
        }
        return $element;
    }

    /**
     * Given a child DOMNode, tries to determine how to map the data into
     * object instance members.  If no mapping is defined, Extension_Element
     * objects are created and stored in an array.
     *
     * @param DOMNode $child The DOMNode needed to be handled
     */
    protected function takeChildFromDOM($child)
    {
        if ($child->nodeType == XML_TEXT_NODE) {
            $this->_text = $child->nodeValue;
        } else {
            $extensionElement = new Zend_Gdata_App_Extension_Element();
            $extensionElement->transferFromDOM($child);
            $this->_extensionElements[] = $extensionElement;
        }
    }

    /**
     * Given a DOMNode representing an attribute, tries to map the data into
     * instance members.  If no mapping is defined, the name and value are
     * stored in an array.
     *
     * @param DOMNode $attribute The DOMNode attribute needed to be handled
     */
    protected function takeAttributeFromDOM($attribute)
    {
        $arrayIndex = ($attribute->namespaceURI != '')?(
                $attribute->namespaceURI . ':' . $attribute->name):
                $attribute->name;
        $this->_extensionAttributes[$arrayIndex] =
                array('namespaceUri' => $attribute->namespaceURI,
                      'name' => $attribute->localName,
                      'value' => $attribute->nodeValue);
    }

    /**
     * Transfers each child and attribute into member variables.
     * This is called when XML is received over the wire and the data
     * model needs to be built to represent this XML.
     *
     * @param DOMNode $node The DOMNode that represents this object's data
     */
    public function transferFromDOM($node)
    {
        foreach ($node->childNodes as $child) {
            $this->takeChildFromDOM($child);
        }
        foreach ($node->attributes as $attribute) {
            $this->takeAttributeFromDOM($attribute);
        }
    }

    /**
     * Parses the provided XML text and generates data model classes for
     * each know element by turning the XML text into a DOM tree and calling
     * transferFromDOM($element).  The first data model element with the same
     * name as $this->_rootElement is used and the child elements are
     * recursively parsed.
     *
     * @param string $xml The XML text to parse
     */
    public function transferFromXML($xml)
    {
        if ($xml) {
            // Load the feed as an XML DOMDocument object
            @ini_set('track_errors', 1);
            $doc = new DOMDocument();
            $success = @$doc->loadXML($xml);
            @ini_restore('track_errors');
            if (!$success) {
                #require_once 'Zend/Gdata/App/Exception.php';
                throw new Zend_Gdata_App_Exception("DOMDocument cannot parse XML: $php_errormsg");
            }
            $element = $doc->getElementsByTagName($this->_rootElement)->item(0);
            if (!$element) {
                #require_once 'Zend/Gdata/App/Exception.php';
                throw new Zend_Gdata_App_Exception('No root <' . $this->_rootElement . '> element');
            }
            $this->transferFromDOM($element);
        } else {
            #require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception('XML passed to transferFromXML cannot be null');
        }
    }

    /**
     * Converts this element and all children into XML text using getDOM()
     *
     * @return string XML content
     */
    public function saveXML()
    {
        $element = $this->getDOM();
        return $element->ownerDocument->saveXML($element);
    }

    /**
     * Alias for saveXML() returns XML content for this element and all
     * children
     *
     * @return string XML content
     */
    public function getXML()
    {
        return $this->saveXML();
    }

    /**
     * Alias for saveXML()
     *
     * Can be overridden by children to provide more complex representations
     * of entries.
     *
     * @return string Encoded string content
     */
    public function encode()
    {
        return $this->saveXML();
    }

    /**
     * Get the full version of a namespace prefix
     *
     * Looks up a prefix (atom:, etc.) in the list of registered
     * namespaces and returns the full namespace URI if
     * available. Returns the prefix, unmodified, if it's not
     * registered.
     *
     * @param string $prefix The namespace prefix to lookup.
     * @param integer $majorVersion The major protocol version in effect.
     *        Defaults to '1'.
     * @param integer $minorVersion The minor protocol version in effect.
     *        Defaults to null (use latest).
     * @return string
     */
    public function lookupNamespace($prefix,
                                    $majorVersion = 1,
                                    $minorVersion = null)
    {
        // Check for a memoized result
        $key = $prefix . ' ' .
               ($majorVersion === null ? 'NULL' : $majorVersion) .
               ' '. ($minorVersion === null ? 'NULL' : $minorVersion);
        if (array_key_exists($key, self::$_namespaceLookupCache))
          return self::$_namespaceLookupCache[$key];
        // If no match, return the prefix by default
        $result = $prefix;

        // Find tuple of keys that correspond to the namespace we should use
        if (isset($this->_namespaces[$prefix])) {
            // Major version search
            $nsData = $this->_namespaces[$prefix];
            $foundMajorV = Zend_Gdata_App_Util::findGreatestBoundedValue(
                    $majorVersion, $nsData);
            // Minor version search
            $nsData = $nsData[$foundMajorV];
            $foundMinorV = Zend_Gdata_App_Util::findGreatestBoundedValue(
                    $minorVersion, $nsData);
            // Extract NS
            $result = $nsData[$foundMinorV];
        }

        // Memoize result
        self::$_namespaceLookupCache[$key] = $result;

        return $result;
    }

    /**
     * Add a namespace and prefix to the registered list
     *
     * Takes a prefix and a full namespace URI and adds them to the
     * list of registered namespaces for use by
     * $this->lookupNamespace().
     *
     * WARNING: Currently, registering a namespace will NOT invalidate any
     *          memoized data stored in $_namespaceLookupCache. Under normal
     *          use, this behavior is acceptable. If you are adding
     *          contradictory data to the namespace lookup table, you must
     *          call flushNamespaceLookupCache().
     *
     * @param  string $prefix The namespace prefix
     * @param  string $namespaceUri The full namespace URI
     * @param integer $majorVersion The major protocol version in effect.
     *        Defaults to '1'.
     * @param integer $minorVersion The minor protocol version in effect.
     *        Defaults to null (use latest).
     * @return void
     */
    public function registerNamespace($prefix,
                                      $namespaceUri,
                                      $majorVersion = 1,
                                      $minorVersion = 0)
    {
        $this->_namespaces[$prefix][$majorVersion][$minorVersion] =
        $namespaceUri;
    }

    /**
     * Flush namespace lookup cache.
     *
     * Empties the namespace lookup cache. Call this function if you have
     * added data to the namespace lookup table that contradicts values that
     * may have been cached during a previous call to lookupNamespace().
     */
    public static function flushNamespaceLookupCache()
    {
        self::$_namespaceLookupCache = array();
    }

    /**
     * Add an array of namespaces to the registered list.
     *
     * Takes an array in the format of:
     * namespace prefix, namespace URI, major protocol version,
     * minor protocol version and adds them with calls to ->registerNamespace()
     *
     * @param array $namespaceArray An array of namespaces.
     * @return void
     */
    public function registerAllNamespaces($namespaceArray)
    {
        foreach($namespaceArray as $namespace) {
                $this->registerNamespace(
                    $namespace[0], $namespace[1], $namespace[2], $namespace[3]);
        }
    }


    /**
     * Magic getter to allow access like $entry->foo to call $entry->getFoo()
     * Alternatively, if no getFoo() is defined, but a $_foo protected variable
     * is defined, this is returned.
     *
     * TODO Remove ability to bypass getFoo() methods??
     *
     * @param string $name The variable name sought
     */
    public function __get($name)
    {
        $method = 'get'.ucfirst($name);
        if (method_exists($this, $method)) {
            return call_user_func(array(&$this, $method));
        } else if (property_exists($this, "_${name}")) {
            return $this->{'_' . $name};
        } else {
            #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Property ' . $name . ' does not exist');
        }
    }

    /**
     * Magic setter to allow acces like $entry->foo='bar' to call
     * $entry->setFoo('bar') automatically.
     *
     * Alternatively, if no setFoo() is defined, but a $_foo protected variable
     * is defined, this is returned.
     *
     * TODO Remove ability to bypass getFoo() methods??
     *
     * @param string $name
     * @param string $value
     */
    public function __set($name, $val)
    {
        $method = 'set'.ucfirst($name);
        if (method_exists($this, $method)) {
            return call_user_func(array(&$this, $method), $val);
        } else if (isset($this->{'_' . $name}) || ($this->{'_' . $name} === null)) {
            $this->{'_' . $name} = $val;
        } else {
            #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Property ' . $name . '  does not exist');
        }
    }

    /**
     * Magic __isset method
     *
     * @param string $name
     */
    public function __isset($name)
    {
        $rc = new ReflectionClass(get_class($this));
        $privName = '_' . $name;
        if (!($rc->hasProperty($privName))) {
            #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Property ' . $name . ' does not exist');
        } else {
            if (isset($this->{$privName})) {
                if (is_array($this->{$privName})) {
                    if (count($this->{$privName}) > 0) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return true;
                }
            } else {
                return false;
            }
        }
    }

    /**
     * Magic __unset method
     *
     * @param string $name
     */
    public function __unset($name)
    {
        if (isset($this->{'_' . $name})) {
            if (is_array($this->{'_' . $name})) {
                $this->{'_' . $name} = array();
            } else {
                $this->{'_' . $name} = null;
            }
        }
    }

    /**
     * Magic toString method allows using this directly via echo
     * Works best in PHP >= 4.2.0
     *
     * @return string The text representation of this object
     */
    public function __toString()
    {
        return $this->getText();
    }

}
