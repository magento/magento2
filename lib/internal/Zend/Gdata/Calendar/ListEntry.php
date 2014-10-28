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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: ListEntry.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Entry
 */
#require_once 'Zend/Gdata/Entry.php';

/**
 * @see Zend_Calendar_Extension_AccessLevel
 */
#require_once 'Zend/Gdata/Calendar/Extension/AccessLevel.php';

/**
 * @see Zend_Calendar_Extension_Color
 */
#require_once 'Zend/Gdata/Calendar/Extension/Color.php';

/**
 * @see Zend_Calendar_Extension_Hidden
 */
#require_once 'Zend/Gdata/Calendar/Extension/Hidden.php';

/**
 * @see Zend_Calendar_Extension_Selected
 */
#require_once 'Zend/Gdata/Calendar/Extension/Selected.php';

/**
 * @see Zend_Gdata_Extension_EventStatus
 */
#require_once 'Zend/Gdata/Extension/EventStatus.php';

/**
 * @see Zend_Gdata_Extension_Visibility
 */
#require_once 'Zend/Gdata/Extension/Visibility.php';


/**
 * @see Zend_Extension_Where
 */
#require_once 'Zend/Gdata/Extension/Where.php';

/**
 * Represents a Calendar entry in the Calendar data API meta feed of a user's
 * calendars.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Calendar
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Calendar_ListEntry extends Zend_Gdata_Entry
{

    protected $_color = null;
    protected $_accessLevel = null;
    protected $_hidden = null;
    protected $_selected = null;
    protected $_timezone = null;
    protected $_where = array();

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Calendar::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_accessLevel != null) {
            $element->appendChild($this->_accessLevel->getDOM($element->ownerDocument));
        }
        if ($this->_color != null) {
            $element->appendChild($this->_color->getDOM($element->ownerDocument));
        }
        if ($this->_hidden != null) {
            $element->appendChild($this->_hidden->getDOM($element->ownerDocument));
        }
        if ($this->_selected != null) {
            $element->appendChild($this->_selected->getDOM($element->ownerDocument));
        }
        if ($this->_timezone != null) {
            $element->appendChild($this->_timezone->getDOM($element->ownerDocument));
        }
        if ($this->_where != null) {
            foreach ($this->_where as $where) {
                $element->appendChild($where->getDOM($element->ownerDocument));
            }
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('gCal') . ':' . 'accesslevel';
            $accessLevel = new Zend_Gdata_Calendar_Extension_AccessLevel();
            $accessLevel->transferFromDOM($child);
            $this->_accessLevel = $accessLevel;
            break;
        case $this->lookupNamespace('gCal') . ':' . 'color';
            $color = new Zend_Gdata_Calendar_Extension_Color();
            $color->transferFromDOM($child);
            $this->_color = $color;
            break;
        case $this->lookupNamespace('gCal') . ':' . 'hidden';
            $hidden = new Zend_Gdata_Calendar_Extension_Hidden();
            $hidden->transferFromDOM($child);
            $this->_hidden = $hidden;
            break;
        case $this->lookupNamespace('gCal') . ':' . 'selected';
            $selected = new Zend_Gdata_Calendar_Extension_Selected();
            $selected->transferFromDOM($child);
            $this->_selected = $selected;
            break;
        case $this->lookupNamespace('gCal') . ':' . 'timezone';
            $timezone = new Zend_Gdata_Calendar_Extension_Timezone();
            $timezone->transferFromDOM($child);
            $this->_timezone = $timezone;
            break;
        case $this->lookupNamespace('gd') . ':' . 'where';
            $where = new Zend_Gdata_Extension_Where();
            $where->transferFromDOM($child);
            $this->_where[] = $where;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function getAccessLevel()
    {
        return $this->_accessLevel;
    }

    /**
     * @param Zend_Gdata_Calendar_Extension_AccessLevel $value
     * @return Zend_Gdata_Extension_ListEntry Provides a fluent interface
     */
    public function setAccessLevel($value)
    {
        $this->_accessLevel = $value;
        return $this;
    }
    public function getColor()
    {
        return $this->_color;
    }

    /**
     * @param Zend_Gdata_Calendar_Extension_Color $value
     * @return Zend_Gdata_Extension_ListEntry Provides a fluent interface
     */
    public function setColor($value)
    {
        $this->_color = $value;
        return $this;
    }

    public function getHidden()
    {
        return $this->_hidden;
    }

    /**
     * @param Zend_Gdata_Calendar_Extension_Hidden $value
     * @return Zend_Gdata_Extension_ListEntry Provides a fluent interface
     */
    public function setHidden($value)
    {
        $this->_hidden = $value;
        return $this;
    }

    public function getSelected()
    {
        return $this->_selected;
    }

    /**
     * @param Zend_Gdata_Calendar_Extension_Selected $value
     * @return Zend_Gdata_Extension_ListEntry Provides a fluent interface
     */
    public function setSelected($value)
    {
        $this->_selected = $value;
        return $this;
    }

    public function getTimezone()
    {
        return $this->_timezone;
    }

    /**
     * @param Zend_Gdata_Calendar_Extension_Timezone $value
     * @return Zend_Gdata_Extension_ListEntry Provides a fluent interface
     */
    public function setTimezone($value)
    {
        $this->_timezone = $value;
        return $this;
    }

    public function getWhere()
    {
        return $this->_where;
    }

    /**
     * @param Zend_Gdata_Extension_Where $value
     * @return Zend_Gdata_Extension_ListEntry Provides a fluent interface
     */
    public function setWhere($value)
    {
        $this->_where = $value;
        return $this;
    }

}
