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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Dojo_Form_Element_ValidationTextBox */
#require_once 'Zend/Dojo/Form/Element/ValidationTextBox.php';

/**
 * NumberSpinner dijit
 *
 * @uses       Zend_Dojo_Form_Element_ValidationTextBox
 * @package    Zend_Dojo
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Dojo_Form_Element_NumberSpinner extends Zend_Dojo_Form_Element_ValidationTextBox
{
    /**
     * Use NumberSpinner dijit view helper
     * @var string
     */
    public $helper = 'NumberSpinner';

    /**
     * Set defaultTimeout
     *
     * @param  int $timeout
     * @return Zend_Dojo_Form_Element_NumberSpinner
     */
    public function setDefaultTimeout($timeout)
    {
        $this->setDijitParam('defaultTimeout', (int) $timeout);
        return $this;
    }

    /**
     * Retrieve defaultTimeout
     *
     * @return int|null
     */
    public function getDefaultTimeout()
    {
        return $this->getDijitParam('defaultTimeout');
    }

    /**
     * Set timeoutChangeRate
     *
     * @param  int $rate
     * @return Zend_Dojo_Form_Element_NumberSpinner
     */
    public function setTimeoutChangeRate($rate)
    {
        $this->setDijitParam('timeoutChangeRate', (int) $rate);
        return $this;
    }

    /**
     * Retrieve timeoutChangeRate
     *
     * @return int|null
     */
    public function getTimeoutChangeRate()
    {
        return $this->getDijitParam('timeoutChangeRate');
    }

    /**
     * Set largeDelta
     *
     * @param  int $delta
     * @return Zend_Dojo_Form_Element_NumberSpinner
     */
    public function setLargeDelta($delta)
    {
        $this->setDijitParam('largeDelta', (float) $delta);
        return $this;
    }

    /**
     * Retrieve largeDelta
     *
     * @return int|null
     */
    public function getLargeDelta()
    {
        return $this->getDijitParam('largeDelta');
    }

    /**
     * Set smallDelta
     *
     * @param  int $delta
     * @return Zend_Dojo_Form_Element_NumberSpinner
     */
    public function setSmallDelta($delta)
    {
        $this->setDijitParam('smallDelta', (float) $delta);
        return $this;
    }

    /**
     * Retrieve smallDelta
     *
     * @return int|null
     */
    public function getSmallDelta()
    {
        return $this->getDijitParam('smallDelta');
    }

    /**
     * Set intermediateChanges flag
     *
     * @param  bool $flag
     * @return Zend_Dojo_Form_Element_TextBox
     */
    public function setIntermediateChanges($flag)
    {
        $this->setDijitParam('intermediateChanges', (bool) $flag);
        return $this;
    }

    /**
     * Retrieve intermediateChanges flag
     *
     * @return bool
     */
    public function getIntermediateChanges()
    {
        if (!$this->hasDijitParam('intermediateChanges')) {
            return false;
        }
        return $this->getDijitParam('intermediateChanges');
    }

    /**
     * Set rangeMessage
     *
     * @param  string $message
     * @return Zend_Dojo_Form_Element_NumberSpinner
     */
    public function setRangeMessage($message)
    {
        $this->setDijitParam('rangeMessage', (string) $message);
        return $this;
    }

    /**
     * Retrieve rangeMessage
     *
     * @return string|null
     */
    public function getRangeMessage()
    {
        return $this->getDijitParam('rangeMessage');
    }

    /**
     * Set minimum value
     *
     * @param  int $value
     * @return Zend_Dojo_Form_Element_NumberSpinner
     */
    public function setMin($value)
    {
        $constraints = array();
        if ($this->hasDijitParam('constraints')) {
            $constraints = $this->getDijitParam('constraints');
        }
        $constraints['min'] = (float) $value;
        $this->setDijitParam('constraints', $constraints);
        return $this;
    }

    /**
     * Get minimum value
     *
     * @return null|int
     */
    public function getMin()
    {
        if (!$this->hasDijitParam('constraints')) {
            return null;
        }
        $constraints = $this->getDijitParam('constraints');
        if (!array_key_exists('min', $constraints)) {
            return null;
        }
        return $constraints['min'];
    }

    /**
     * Set maximum value
     *
     * @param  int $value
     * @return Zend_Dojo_Form_Element_NumberSpinner
     */
    public function setMax($value)
    {
        $constraints = array();
        if ($this->hasDijitParam('constraints')) {
            $constraints = $this->getDijitParam('constraints');
        }
        $constraints['max'] = (float) $value;
        $this->setDijitParam('constraints', $constraints);
        return $this;
    }

    /**
     * Get maximum value
     *
     * @return null|int
     */
    public function getMax()
    {
        if (!$this->hasDijitParam('constraints')) {
            return null;
        }
        $constraints = $this->getDijitParam('constraints');
        if (!array_key_exists('max', $constraints)) {
            return null;
        }
        return $constraints['max'];
    }
}
