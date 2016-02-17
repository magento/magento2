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
 * @see Zend_Gdata_Extension_Reminder
 */
#require_once 'Zend/Gdata/Extension/Reminder.php';

/**
 * Represents the gd:when element
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gdata
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Extension_When extends Zend_Gdata_Extension
{

    protected $_rootElement = 'when';
    protected $_reminders = array();
    protected $_startTime = null;
    protected $_valueString = null;
    protected $_endTime = null;

    public function __construct($startTime = null, $endTime = null,
            $valueString = null, $reminders = null)
    {
        parent::__construct();
        $this->_startTime = $startTime;
        $this->_endTime = $endTime;
        $this->_valueString = $valueString;
        $this->_reminders = $reminders;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_startTime !== null) {
            $element->setAttribute('startTime', $this->_startTime);
        }
        if ($this->_endTime !== null) {
            $element->setAttribute('endTime', $this->_endTime);
        }
        if ($this->_valueString !== null) {
            $element->setAttribute('valueString', $this->_valueString);
        }
        if ($this->_reminders !== null) {
            foreach ($this->_reminders as $reminder) {
                $element->appendChild(
                        $reminder->getDOM($element->ownerDocument));
            }
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
            case $this->lookupNamespace('gd') . ':' . 'reminder';
                $reminder = new Zend_Gdata_Extension_Reminder();
                $reminder->transferFromDOM($child);
                $this->_reminders[] = $reminder;
                break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
            case 'startTime':
                $this->_startTime = $attribute->nodeValue;
                break;
            case 'endTime':
                $this->_endTime = $attribute->nodeValue;
                break;
            case 'valueString':
                $this->_valueString = $attribute->nodeValue;
                break;
            default:
                parent::takeAttributeFromDOM($attribute);
        }
    }

    public function __toString()
    {
        if ($this->_valueString)
            return $this->_valueString;
        else {
            return 'Starts: ' . $this->getStartTime() . ' ' .
                   'Ends: ' .  $this->getEndTime();
        }
    }

    public function getStartTime()
    {
        return $this->_startTime;
    }

    public function setStartTime($value)
    {
        $this->_startTime = $value;
        return $this;
    }

    public function getEndTime()
    {
        return $this->_endTime;
    }

    public function setEndTime($value)
    {
        $this->_endTime = $value;
        return $this;
    }

    public function getValueString()
    {
        return $this->_valueString;
    }

    public function setValueString($value)
    {
        $this->_valueString = $value;
        return $this;
    }

    public function getReminders()
    {
        return $this->_reminders;
    }

    public function setReminders($value)
    {
        $this->_reminders = $value;
        return $this;
    }

}
