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

/** Zend_Dojo_Form_Element_Slider */
#require_once 'Zend/Dojo/Form/Element/Slider.php';

/**
 * VerticalSlider dijit
 *
 * @uses       Zend_Dojo_Form_Element_Slider
 * @package    Zend_Dojo
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Dojo_Form_Element_VerticalSlider extends Zend_Dojo_Form_Element_Slider
{
    /**
     * Use VerticalSlider dijit view helper
     * @var string
     */
    public $helper = 'VerticalSlider';

    /**
     * Get left decoration data
     *
     * @return array
     */
    public function getLeftDecoration()
    {
        if ($this->hasDijitParam('leftDecoration')) {
            return $this->getDijitParam('leftDecoration');
        }
        return array();
    }

    /**
     * Set dijit to use with left decoration
     *
     * @param mixed $dijit
     * @return Zend_Dojo_Form_Element_HorizontalSlider
     */
    public function setLeftDecorationDijit($dijit)
    {
        $decoration = $this->getLeftDecoration();
        $decoration['dijit'] = (string) $dijit;
        $this->setDijitParam('leftDecoration', $decoration);
        return $this;
    }

    /**
     * Set container to use with left decoration
     *
     * @param mixed $container
     * @return Zend_Dojo_Form_Element_HorizontalSlider
     */
    public function setLeftDecorationContainer($container)
    {
        $decoration = $this->getLeftDecoration();
        $decoration['container'] = (string) $container;
        $this->setDijitParam('leftDecoration', $decoration);
        return $this;
    }

    /**
     * Set labels to use with left decoration
     *
     * @param  array $labels
     * @return Zend_Dojo_Form_Element_HorizontalSlider
     */
    public function setLeftDecorationLabels(array $labels)
    {
        $decoration = $this->getLeftDecoration();
        $decoration['labels'] = array_values($labels);
        $this->setDijitParam('leftDecoration', $decoration);
        return $this;
    }

    /**
     * Set params to use with left decoration
     *
     * @param  array $params
     * @return Zend_Dojo_Form_Element_HorizontalSlider
     */
    public function setLeftDecorationParams(array $params)
    {
        $decoration = $this->getLeftDecoration();
        $decoration['params'] = $params;
        $this->setDijitParam('leftDecoration', $decoration);
        return $this;
    }

    /**
     * Set attribs to use with left decoration
     *
     * @param  array $attribs
     * @return Zend_Dojo_Form_Element_HorizontalSlider
     */
    public function setLeftDecorationAttribs(array $attribs)
    {
        $decoration = $this->getLeftDecoration();
        $decoration['attribs'] = $attribs;
        $this->setDijitParam('leftDecoration', $decoration);
        return $this;
    }

    /**
     * Get right decoration data
     *
     * @return array
     */
    public function getRightDecoration()
    {
        if ($this->hasDijitParam('rightDecoration')) {
            return $this->getDijitParam('rightDecoration');
        }
        return array();
    }

    /**
     * Set dijit to use with right decoration
     *
     * @param mixed $dijit
     * @return Zend_Dojo_Form_Element_HorizontalSlider
     */
    public function setRightDecorationDijit($dijit)
    {
        $decoration = $this->getRightDecoration();
        $decoration['dijit'] = (string) $dijit;
        $this->setDijitParam('rightDecoration', $decoration);
        return $this;
    }

    /**
     * Set container to use with right decoration
     *
     * @param mixed $container
     * @return Zend_Dojo_Form_Element_HorizontalSlider
     */
    public function setRightDecorationContainer($container)
    {
        $decoration = $this->getRightDecoration();
        $decoration['container'] = (string) $container;
        $this->setDijitParam('rightDecoration', $decoration);
        return $this;
    }

    /**
     * Set labels to use with right decoration
     *
     * @param  array $labels
     * @return Zend_Dojo_Form_Element_HorizontalSlider
     */
    public function setRightDecorationLabels(array $labels)
    {
        $decoration = $this->getRightDecoration();
        $decoration['labels'] = array_values($labels);
        $this->setDijitParam('rightDecoration', $decoration);
        return $this;
    }

    /**
     * Set params to use with right decoration
     *
     * @param  array $params
     * @return Zend_Dojo_Form_Element_HorizontalSlider
     */
    public function setRightDecorationParams(array $params)
    {
        $decoration = $this->getRightDecoration();
        $decoration['params'] = $params;
        $this->setDijitParam('rightDecoration', $decoration);
        return $this;
    }

    /**
     * Set attribs to use with right decoration
     *
     * @param  array $attribs
     * @return Zend_Dojo_Form_Element_HorizontalSlider
     */
    public function setRightDecorationAttribs(array $attribs)
    {
        $decoration = $this->getRightDecoration();
        $decoration['attribs'] = $attribs;
        $this->setDijitParam('rightDecoration', $decoration);
        return $this;
    }
}
