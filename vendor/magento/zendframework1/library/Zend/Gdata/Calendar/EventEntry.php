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
 * @subpackage Calendar
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Gdata_Entry
 */
#require_once 'Zend/Gdata/Entry.php';

/**
 * @see Zend_Gdata_Kind_EventEntry
 */
#require_once 'Zend/Gdata/Kind/EventEntry.php';

/**
 * @see Zend_Gdata_Calendar_Extension_SendEventNotifications
 */
#require_once 'Zend/Gdata/Calendar/Extension/SendEventNotifications.php';

/**
 * @see Zend_Gdata_Calendar_Extension_Timezone
 */
#require_once 'Zend/Gdata/Calendar/Extension/Timezone.php';

/**
 * @see Zend_Gdata_Calendar_Extension_Link
 */
#require_once 'Zend/Gdata/Calendar/Extension/Link.php';

/**
 * @see Zend_Gdata_Calendar_Extension_QuickAdd
 */
#require_once 'Zend/Gdata/Calendar/Extension/QuickAdd.php';

/**
 * Data model class for a Google Calendar Event Entry
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Calendar
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Calendar_EventEntry extends Zend_Gdata_Kind_EventEntry
{

    protected $_entryClassName = 'Zend_Gdata_Calendar_EventEntry';
    protected $_sendEventNotifications = null;
    protected $_timezone = null;
    protected $_quickadd = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Calendar::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_sendEventNotifications != null) {
            $element->appendChild($this->_sendEventNotifications->getDOM($element->ownerDocument));
        }
        if ($this->_timezone != null) {
            $element->appendChild($this->_timezone->getDOM($element->ownerDocument));
        }
        if ($this->_quickadd != null) {
            $element->appendChild($this->_quickadd->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;

        switch ($absoluteNodeName) {
            case $this->lookupNamespace('gCal') . ':' . 'sendEventNotifications';
                $sendEventNotifications = new Zend_Gdata_Calendar_Extension_SendEventNotifications();
                $sendEventNotifications->transferFromDOM($child);
                $this->_sendEventNotifications = $sendEventNotifications;
                break;
            case $this->lookupNamespace('gCal') . ':' . 'timezone';
                $timezone = new Zend_Gdata_Calendar_Extension_Timezone();
                $timezone->transferFromDOM($child);
                $this->_timezone = $timezone;
                break;
            case $this->lookupNamespace('atom') . ':' . 'link';
                $link = new Zend_Gdata_Calendar_Extension_Link();
                $link->transferFromDOM($child);
                $this->_link[] = $link;
                break;
            case $this->lookupNamespace('gCal') . ':' . 'quickadd';
                $quickadd = new Zend_Gdata_Calendar_Extension_QuickAdd();
                $quickadd->transferFromDOM($child);
                $this->_quickadd = $quickadd;
                break;
            default:
                parent::takeChildFromDOM($child);
                break;
        }
    }

    public function getSendEventNotifications()
    {
        return $this->_sendEventNotifications;
    }

    public function setSendEventNotifications($value)
    {
        $this->_sendEventNotifications = $value;
        return $this;
    }

    public function getTimezone()
    {
        return $this->_timezone;
    }

    /**
     * @param Zend_Gdata_Calendar_Extension_Timezone $value
     * @return Zend_Gdata_Extension_EventEntry Provides a fluent interface
     */
    public function setTimezone($value)
    {
        $this->_timezone = $value;
        return $this;
    }

    public function getQuickAdd()
    {
        return $this->_quickadd;
    }

    /**
     * @param Zend_Gdata_Calendar_Extension_QuickAdd $value
     * @return Zend_Gdata_Extension_ListEntry Provides a fluent interface
     */
    public function setQuickAdd($value)
    {
        $this->_quickadd = $value;
        return $this;
    }

}
