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
 * @subpackage Gbase
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Entry.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Entry
 */
#require_once 'Zend/Gdata/Entry.php';

/**
 * @see Zend_Gdata_Gbase_Extension_BaseAttribute
 */
#require_once 'Zend/Gdata/Gbase/Extension/BaseAttribute.php';

/**
 * Base class for working with Google Base entries.
 *
 * @link http://code.google.com/apis/base/
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gbase
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Gbase_Entry extends Zend_Gdata_Entry
{

    /**
     * Name of the base class for Google Base entries
     *
     * var @string
     */
    protected $_entryClassName = 'Zend_Gdata_Gbase_Entry';

    /**
     * Google Base attribute elements in the 'g' namespace
     *
     * @var array
     */
    protected $_baseAttributes = array();

    /**
     * Constructs a new Zend_Gdata_Gbase_ItemEntry object.
     * @param DOMElement $element (optional) The DOMElement on which to base this object.
     */
    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Gbase::$namespaces);
        parent::__construct($element);
    }

    /**
     * Retrieves a DOMElement which corresponds to this element and all
     * child properties.  This is used to build an entry back into a DOM
     * and eventually XML text for application storage/persistence.
     *
     * @param DOMDocument $doc The DOMDocument used to construct DOMElements
     * @return DOMElement The DOMElement representing this element and all
     *          child properties.
     */
    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        foreach ($this->_baseAttributes as $baseAttribute) {
            $element->appendChild($baseAttribute->getDOM($element->ownerDocument));
        }
        return $element;
    }

    /**
     * Creates individual Entry objects of the appropriate type and
     * stores them as members of this entry based upon DOM data.
     *
     * @param DOMNode $child The DOMNode to process
     */
    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;

        if (strstr($absoluteNodeName, $this->lookupNamespace('g') . ':')) {
            $baseAttribute = new Zend_Gdata_Gbase_Extension_BaseAttribute();
            $baseAttribute->transferFromDOM($child);
            $this->_baseAttributes[] = $baseAttribute;
        } else {
            parent::takeChildFromDOM($child);
        }
    }

    /**
     * Get the value of the itme_type
     *
     * @return Zend_Gdata_Gbase_Extension_ItemType The requested object.
     */
    public function getItemType()
    {
        $itemType = $this->getGbaseAttribute('item_type');
        if (is_object($itemType[0])) {
          return $itemType[0];
        } else {
          return null;
        }
    }

    /**
     * Return all the Base attributes
     * @return Zend_Gdata_Gbase_Extension_BaseAttribute
     */
    public function getGbaseAttributes() {
        return $this->_baseAttributes;
    }

    /**
     * Return an array of Base attributes that match the given attribute name
     *
     * @param string $name The name of the Base attribute to look for
     * @return array $matches Array that contains the matching list of Base attributes
     */
    public function getGbaseAttribute($name)
    {
        $matches = array();
        for ($i = 0; $i < count($this->_baseAttributes); $i++) {
            $baseAttribute = $this->_baseAttributes[$i];
            if ($baseAttribute->rootElement == $name &&
                $baseAttribute->rootNamespaceURI == $this->lookupNamespace('g')) {
                $matches[] = &$this->_baseAttributes[$i];
            }
        }
        return $matches;
    }

}
