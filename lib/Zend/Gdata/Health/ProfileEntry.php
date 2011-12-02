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
 * @subpackage Health
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: ProfileEntry.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Entry
 */
#require_once 'Zend/Gdata/Entry.php';

/**
 * @see Zend_Gdata_Health_Extension_Ccr
 */
#require_once 'Zend/Gdata/Health/Extension/Ccr.php';

/**
 * Concrete class for working with Health profile entries.
 *
 * @link http://code.google.com/apis/health/
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Health
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Health_ProfileEntry extends Zend_Gdata_Entry
{
    /**
     * The classname for individual profile entry elements.
     *
     * @var string
     */
    protected $_entryClassName = 'Zend_Gdata_Health_ProfileEntry';

    /**
     * Google Health CCR data
     *
     * @var Zend_Gdata_Health_Extension_Ccr
     */
    protected $_ccrData = null;

    /**
     * Constructs a new Zend_Gdata_Health_ProfileEntry object.
     * @param DOMElement $element (optional) The DOMElement on which to base this object.
     */
    public function __construct($element = null)
    {
        foreach (Zend_Gdata_Health::$namespaces as $nsPrefix => $nsUri) {
            $this->registerNamespace($nsPrefix, $nsUri);
        }
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
        if ($this->_ccrData !== null) {
          $element->appendChild($this->_ccrData->getDOM($element->ownerDocument));
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

        if (strstr($absoluteNodeName, $this->lookupNamespace('ccr') . ':')) {
            $ccrElement = new Zend_Gdata_Health_Extension_Ccr();
            $ccrElement->transferFromDOM($child);
            $this->_ccrData = $ccrElement;
        } else {
            parent::takeChildFromDOM($child);

        }
    }

    /**
     * Sets the profile entry's CCR data
     * @param string $ccrXMLStr The CCR as an xml string
     * @return Zend_Gdata_Health_Extension_Ccr
     */
    public function setCcr($ccrXMLStr) {
        $ccrElement = null;
        if ($ccrXMLStr != null) {
          $ccrElement = new Zend_Gdata_Health_Extension_Ccr();
          $ccrElement->transferFromXML($ccrXMLStr);
          $this->_ccrData = $ccrElement;
        }
        return $ccrElement;
    }


    /**
     * Returns all the CCR data in a profile entry
     * @return Zend_Gdata_Health_Extension_Ccr
     */
    public function getCcr() {
        return $this->_ccrData;
    }
}
