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

/** Zend_Dojo_Form_Element_Dijit */
#require_once 'Zend/Dojo/Form/Element/Dijit.php';

/**
 * Abstract Slider dijit
 *
 * @uses       Zend_Dojo_Form_Element_Dijit
 * @package    Zend_Dojo
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Slider.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
abstract class Zend_Dojo_Form_Element_Slider extends Zend_Dojo_Form_Element_Dijit
{
    /**
     * Set clickSelect flag
     *
     * @param  bool $clickSelect
     * @return Zend_Dojo_Form_Element_TextBox
     */
    public function setClickSelect($flag)
    {
        $this->setDijitParam('clickSelect', (bool) $flag);
        return $this;
    }

    /**
     * Retrieve clickSelect flag
     *
     * @return bool
     */
    public function getClickSelect()
    {
        if (!$this->hasDijitParam('clickSelect')) {
            return false;
        }
        return $this->getDijitParam('clickSelect');
    }

    /**
     * Set intermediateChanges flag
     *
     * @param  bool $intermediateChanges
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
     * Set showButtons flag
     *
     * @param  bool $showButtons
     * @return Zend_Dojo_Form_Element_TextBox
     */
    public function setShowButtons($flag)
    {
        $this->setDijitParam('showButtons', (bool) $flag);
        return $this;
    }

    /**
     * Retrieve showButtons flag
     *
     * @return bool
     */
    public function getShowButtons()
    {
        if (!$this->hasDijitParam('showButtons')) {
            return false;
        }
        return $this->getDijitParam('showButtons');
    }

    /**
     * Set discreteValues
     *
     * @param  int $value
     * @return Zend_Dojo_Form_Element_TextBox
     */
    public function setDiscreteValues($value)
    {
        $this->setDijitParam('discreteValues', (int) $value);
        return $this;
    }

    /**
     * Retrieve discreteValues
     *
     * @return int|null
     */
    public function getDiscreteValues()
    {
        return $this->getDijitParam('discreteValues');
    }

    /**
     * Set maximum
     *
     * @param  int $value
     * @return Zend_Dojo_Form_Element_TextBox
     */
    public function setMaximum($value)
    {
        $this->setDijitParam('maximum', (int) $value);
        return $this;
    }

    /**
     * Retrieve maximum
     *
     * @return int|null
     */
    public function getMaximum()
    {
        return $this->getDijitParam('maximum');
    }

    /**
     * Set minimum
     *
     * @param  int $value
     * @return Zend_Dojo_Form_Element_TextBox
     */
    public function setMinimum($value)
    {
        $this->setDijitParam('minimum', (int) $value);
        return $this;
    }

    /**
     * Retrieve minimum
     *
     * @return int|null
     */
    public function getMinimum()
    {
        return $this->getDijitParam('minimum');
    }

    /**
     * Set pageIncrement
     *
     * @param  int $value
     * @return Zend_Dojo_Form_Element_TextBox
     */
    public function setPageIncrement($value)
    {
        $this->setDijitParam('pageIncrement', (int) $value);
        return $this;
    }

    /**
     * Retrieve pageIncrement
     *
     * @return int|null
     */
    public function getPageIncrement()
    {
        return $this->getDijitParam('pageIncrement');
    }
}
