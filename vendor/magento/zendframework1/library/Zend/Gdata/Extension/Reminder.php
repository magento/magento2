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
 * Implements the gd:reminder element used to set/retrieve notifications
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gdata
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Extension_Reminder extends Zend_Gdata_Extension
{

    protected $_rootElement = 'reminder';
    protected $_absoluteTime = null;
    protected $_method = null;
    protected $_days = null;
    protected $_hours = null;
    protected $_minutes = null;

    public function __construct($absoluteTime = null, $method = null, $days = null,
            $hours = null, $minutes = null)
    {
        parent::__construct();
        $this->_absoluteTime = $absoluteTime;
        $this->_method = $method;
        $this->_days = $days;
        $this->_hours = $hours;
        $this->_minutes = $minutes;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_absoluteTime !== null) {
            $element->setAttribute('absoluteTime', $this->_absoluteTime);
        }
        if ($this->_method !== null) {
            $element->setAttribute('method', $this->_method);
        }
        if ($this->_days !== null) {
            $element->setAttribute('days', $this->_days);
        }
        if ($this->_hours !== null) {
            $element->setAttribute('hours', $this->_hours);
        }
        if ($this->_minutes !== null) {
            $element->setAttribute('minutes', $this->_minutes);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
            case 'absoluteTime':
                $this->_absoluteTime = $attribute->nodeValue;
                break;
            case 'method':
                $this->_method = $attribute->nodeValue;
                break;
            case 'days':
                $this->_days = $attribute->nodeValue;
                break;
            case 'hours':
                $this->_hours = $attribute->nodeValue;
                break;
            case 'minutes':
                $this->_minutes = $attribute->nodeValue;
                break;
            default:
                parent::takeAttributeFromDOM($attribute);
        }
    }

    public function __toString()
    {
        $s = '';
        if ($this->_absoluteTime)
            $s = " at " . $this->_absoluteTime;
        else if ($this->_days)
            $s = " in " . $this->_days . " days";
        else if ($this->_hours)
            $s = " in " . $this->_hours . " hours";
        else if ($this->_minutes)
            $s = " in " . $this->_minutes . " minutes";
        return $this->_method . $s;
    }

    public function getAbsoluteTime()
    {
        return $this->_absoluteTime;
    }

    public function setAbsoluteTime($value)
    {
        $this->_absoluteTime = $value;
        return $this;
    }

    public function getDays()
    {
        return $this->_days;
    }

    public function setDays($value)
    {
        $this->_days = $value;
        return $this;
    }
    public function getHours()
    {
        return $this->_hours;
    }

    public function setHours($value)
    {
        $this->_hours = $value;
        return $this;
    }

    public function getMinutes()
    {
        return $this->_minutes;
    }

    public function setMinutes($value)
    {
        $this->_minutes = $value;
        return $this;
    }

    public function getMethod()
    {
        return $this->_method;
    }

    public function setMethod($value)
    {
        $this->_method = $value;
        return $this;
    }

}
