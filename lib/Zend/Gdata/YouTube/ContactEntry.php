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
 * @subpackage YouTube
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: ContactEntry.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_YouTube_UserProfileEntry
 */
#require_once 'Zend/Gdata/YouTube/UserProfileEntry.php';

/**
 * @see Zend_Gdata_YouTube_Extension_Status
 */
#require_once 'Zend/Gdata/YouTube/Extension/Status.php';

/**
 * The YouTube contacts flavor of an Atom Entry with media support
 * Represents a an individual contact
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage YouTube
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_YouTube_ContactEntry extends Zend_Gdata_YouTube_UserProfileEntry
{

    /**
     * The classname for individual feed elements.
     *
     * @var string
     */
    protected $_entryClassName = 'Zend_Gdata_YouTube_ContactEntry';

    /**
     * Status of the user as a contact
     *
     * @var string
     */
    protected $_status = null;

    /**
     * Constructs a new Contact Entry object, to represent
     * an individual contact for a user
     *
     * @param DOMElement $element (optional) DOMElement from which this
     *          object should be constructed.
     */
    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct($element);
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
        if ($this->_status != null) {
            $element->appendChild($this->_status->getDOM($element->ownerDocument));
        }
        return $element;
    }

    /**
     * Creates individual Entry objects of the appropriate type and
     * stores them in the $_entry array based upon DOM data.
     *
     * @param DOMNode $child The DOMNode to process
     */
    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('yt') . ':' . 'status':
            $status = new Zend_Gdata_YouTube_Extension_Status();
            $status->transferFromDOM($child);
            $this->_status = $status;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    /**
     * Sets the status
     *
     * @param Zend_Gdata_YouTube_Extension_Status $status The status
     * @return Zend_Gdata_YouTube_ContactEntry Provides a fluent interface
     */
    public function setStatus($status = null)
    {
        $this->_status = $status;
        return $this;
    }

    /**
     * Returns the status
     *
     * @return Zend_Gdata_YouTube_Extension_Status  The status
     */
    public function getStatus()
    {
        return $this->_status;
    }

}
