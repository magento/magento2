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
 * @subpackage Gdata
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Gdata_Extension
 */
#require_once 'Zend/Gdata/Extension.php';

/**
 * @see Zend_Gdata_Extension_AttendeeStatus
 */
#require_once 'Zend/Gdata/Extension/AttendeeStatus.php';

/**
 * @see Zend_Gdata_Extension_AttendeeType
 */
#require_once 'Zend/Gdata/Extension/AttendeeType.php';

/**
 * @see Zend_Gdata_Extension_EntryLink
 */
#require_once 'Zend/Gdata/Extension/EntryLink.php';

/**
 * Data model class to represent a participant
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gdata
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Extension_Who extends Zend_Gdata_Extension
{

    protected $_rootElement = 'who';
    protected $_email = null;
    protected $_rel = null;
    protected $_valueString = null;
    protected $_attendeeStatus = null;
    protected $_attendeeType = null;
    protected $_entryLink = null;

    /**
     * Constructs a new Zend_Gdata_Extension_Who object.
     * @param string $email (optional) Email address.
     * @param string $rel (optional) Relationship description.
     * @param string $valueString (optional) Simple string describing this person.
     * @param Zend_Gdata_Extension_AttendeeStatus $attendeeStatus (optional) The status of the attendee.
     * @param Zend_Gdata_Extension_AttendeeType $attendeeType (optional) The type of the attendee.
     * @param string $entryLink URL pointing to an associated entry (Contact kind) describing this person.
     */
    public function __construct($email = null, $rel = null, $valueString = null,
        $attendeeStatus = null, $attendeeType = null, $entryLink = null)
    {
        parent::__construct();
        $this->_email = $email;
        $this->_rel = $rel;
        $this->_valueString = $valueString;
        $this->_attendeeStatus = $attendeeStatus;
        $this->_attendeeType = $attendeeType;
        $this->_entryLink = $entryLink;
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
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_email !== null) {
            $element->setAttribute('email', $this->_email);
        }
        if ($this->_rel !== null) {
            $element->setAttribute('rel', $this->_rel);
        }
        if ($this->_valueString !== null) {
            $element->setAttribute('valueString', $this->_valueString);
        }
        if ($this->_attendeeStatus !== null) {
            $element->appendChild($this->_attendeeStatus->getDOM($element->ownerDocument));
        }
        if ($this->_attendeeType !== null) {
            $element->appendChild($this->_attendeeType->getDOM($element->ownerDocument));
        }
        if ($this->_entryLink !== null) {
            $element->appendChild($this->_entryLink->getDOM($element->ownerDocument));
        }
        return $element;
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
        switch ($attribute->localName) {
        case 'email':
            $this->_email = $attribute->nodeValue;
            break;
        case 'rel':
            $this->_rel = $attribute->nodeValue;
            break;
        case 'valueString':
            $this->_valueString = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
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
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('gd') . ':' . 'attendeeStatus':
            $attendeeStatus = new Zend_Gdata_Extension_AttendeeStatus();
            $attendeeStatus->transferFromDOM($child);
            $this->_attendeeStatus = $attendeeStatus;
            break;
        case $this->lookupNamespace('gd') . ':' . 'attendeeType':
            $attendeeType = new Zend_Gdata_Extension_AttendeeType();
            $attendeeType->transferFromDOM($child);
            $this->_attendeeType = $attendeeType;
            break;
        case $this->lookupNamespace('gd') . ':' . 'entryLink':
            $entryLink = new Zend_Gdata_Extension_EntryLink();
            $entryLink->transferFromDOM($child);
            $this->_entryLink = $entryLink;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    /**
     * Retrieves a human readable string describing this attribute's value.
     *
     * @return string The attribute value.
     */
    public function __toString()
    {
        if ($this->_valueString != null) {
            return $this->_valueString;
        }
        else {
            return parent::__toString();
        }
    }

    /**
     * Get the value for this element's ValueString attribute.
     *
     * @return string The requested attribute.
     */
    public function getValueString()
    {
        return $this->_valueString;
    }

    /**
     * Set the value for this element's ValueString attribute.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Extension_Who The element being modified.
     */
    public function setValueString($value)
    {
        $this->_valueString = $value;
        return $this;
    }

    /**
     * Get the value for this element's Email attribute.
     *
     * @return string The requested attribute.
     */
    public function getEmail()
    {
        return $this->_email;
    }

    /**
     * Set the value for this element's Email attribute.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Extension_Who The element being modified.
     */
    public function setEmail($value)
    {
        $this->_email = $value;
        return $this;
    }

    /**
     * Get the value for this element's Rel attribute.
     *
     * @return string The requested attribute.
     */
    public function getRel()
    {
        return $this->_rel;
    }

    /**
     * Set the value for this element's Rel attribute.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Extension_Who The element being modified.
     */
    public function setRel($value)
    {
        $this->_rel = $value;
        return $this;
    }

    /**
     * Get this entry's AttendeeStatus element.
     *
     * @return Zend_Gdata_Extension_AttendeeStatus The requested entry.
     */
    public function getAttendeeStatus()
    {
        return $this->_attendeeStatus;
    }

    /**
     * Set the child's AttendeeStatus element.
     *
     * @param Zend_Gdata_Extension_AttendeeStatus $value The desired value for this attribute.
     * @return Zend_Gdata_Extension_Who The element being modified.
     */
    public function setAttendeeStatus($value)
    {
        $this->_attendeeStatus = $value;
        return $this;
    }

    /**
     * Get this entry's AttendeeType element.
     *
     * @return Zend_Gdata_Extension_AttendeeType The requested entry.
     */
    public function getAttendeeType()
    {
        return $this->_attendeeType;
    }

    /**
     * Set the child's AttendeeType element.
     *
     * @param Zend_Gdata_Extension_AttendeeType $value The desired value for this attribute.
     * @return Zend_Gdata_Extension_Who The element being modified.
     */
    public function setAttendeeType($value)
    {
        $this->_attendeeType = $value;
        return $this;
    }

}
