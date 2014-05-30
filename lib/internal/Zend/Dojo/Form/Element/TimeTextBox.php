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
 * @package    Zend_Dojo
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Dojo_Form_Element_DateTextBox */
#require_once 'Zend/Dojo/Form/Element/DateTextBox.php';

/**
 * TimeTextBox dijit
 *
 * @uses       Zend_Dojo_Form_Element_DateTextBox
 * @package    Zend_Dojo
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: TimeTextBox.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
class Zend_Dojo_Form_Element_TimeTextBox extends Zend_Dojo_Form_Element_DateTextBox
{
    /**
     * Use TimeTextBox dijit view helper
     * @var string
     */
    public $helper = 'TimeTextBox';

    /**
     * Validate ISO 8601 time format
     *
     * @param  string $format
     * @return true
     * @throws Zend_Form_Element_Exception
     */
    protected function _validateIso8601($format)
    {
        if (!preg_match('/^T\d{2}:\d{2}:\d{2}$/', $format)) {
            #require_once 'Zend/Form/Element/Exception.php';
            throw new Zend_Form_Element_Exception(sprintf('Invalid format "%s" provided; must match T:00:00:00 format', $format));
        }
        return true;
    }

    /**
     * Set time format pattern
     *
     * @param  string $pattern
     * @return Zend_Dojo_Form_Element_NumberTextBox
     */
    public function setTimePattern($pattern)
    {
        $this->setConstraint('timePattern', (string) $pattern);
        return $this;
    }

    /**
     * Retrieve time format pattern
     *
     * @return string|null
     */
    public function getTimePattern()
    {
        return $this->getConstraint('timePattern');
    }

    /**
     * Set clickableIncrement
     *
     * @param  string $format
     * @return Zend_Dojo_Form_Element_NumberTextBox
     */
    public function setClickableIncrement($format)
    {
        $format = (string) $format;
        $this->_validateIso8601($format);
        $this->setConstraint('clickableIncrement', $format);
        return $this;
    }

    /**
     * Retrieve clickableIncrement
     *
     * @return string|null
     */
    public function getClickableIncrement()
    {
        return $this->getConstraint('clickableIncrement');
    }

    /**
     * Set visibleIncrement
     *
     * @param  string $format
     * @return Zend_Dojo_Form_Element_NumberTextBox
     */
    public function setVisibleIncrement($format)
    {
        $format = (string) $format;
        $this->_validateIso8601($format);
        $this->setConstraint('visibleIncrement', $format);
        return $this;
    }

    /**
     * Retrieve visibleIncrement
     *
     * @return string|null
     */
    public function getVisibleIncrement()
    {
        return $this->getConstraint('visibleIncrement');
    }

    /**
     * Set visibleRange
     *
     * @param  string $format
     * @return Zend_Dojo_Form_Element_NumberTextBox
     */
    public function setVisibleRange($format)
    {
        $format = (string) $format;
        $this->_validateIso8601($format);
        $this->setConstraint('visibleRange', $format);
        return $this;
    }

    /**
     * Retrieve visibleRange
     *
     * @return string|null
     */
    public function getVisibleRange()
    {
        return $this->getConstraint('visibleRange');
    }
}
