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

/** Zend_Dojo_Form_Element_Slider */
#require_once 'Zend/Dojo/Form/Element/Slider.php';

/**
 * HorizontalSlider dijit
 *
 * @uses       Zend_Dojo_Form_Element_Slider
 * @package    Zend_Dojo
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: HorizontalSlider.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
class Zend_Dojo_Form_Element_HorizontalSlider extends Zend_Dojo_Form_Element_Slider
{
    /**
     * Use HorizontalSlider dijit view helper
     * @var string
     */
    public $helper = 'HorizontalSlider';

    /**
     * Get top decoration data
     *
     * @return array
     */
    public function getTopDecoration()
    {
        if ($this->hasDijitParam('topDecoration')) {
            return $this->getDijitParam('topDecoration');
        }
        return array();
    }

    /**
     * Set dijit to use with top decoration
     *
     * @param mixed $dijit
     * @return Zend_Dojo_Form_Element_HorizontalSlider
     */
    public function setTopDecorationDijit($dijit)
    {
        $decoration = $this->getTopDecoration();
        $decoration['dijit'] = (string) $dijit;
        $this->setDijitParam('topDecoration', $decoration);
        return $this;
    }

    /**
     * Set container to use with top decoration
     *
     * @param mixed $container
     * @return Zend_Dojo_Form_Element_HorizontalSlider
     */
    public function setTopDecorationContainer($container)
    {
        $decoration = $this->getTopDecoration();
        $decoration['container'] = (string) $container;
        $this->setDijitParam('topDecoration', $decoration);
        return $this;
    }

    /**
     * Set labels to use with top decoration
     *
     * @param  array $labels
     * @return Zend_Dojo_Form_Element_HorizontalSlider
     */
    public function setTopDecorationLabels(array $labels)
    {
        $decoration = $this->getTopDecoration();
        $decoration['labels'] = array_values($labels);
        $this->setDijitParam('topDecoration', $decoration);
        return $this;
    }

    /**
     * Set params to use with top decoration
     *
     * @param  array $params
     * @return Zend_Dojo_Form_Element_HorizontalSlider
     */
    public function setTopDecorationParams(array $params)
    {
        $decoration = $this->getTopDecoration();
        $decoration['params'] = $params;
        $this->setDijitParam('topDecoration', $decoration);
        return $this;
    }

    /**
     * Set attribs to use with top decoration
     *
     * @param  array $attribs
     * @return Zend_Dojo_Form_Element_HorizontalSlider
     */
    public function setTopDecorationAttribs(array $attribs)
    {
        $decoration = $this->getTopDecoration();
        $decoration['attribs'] = $attribs;
        $this->setDijitParam('topDecoration', $decoration);
        return $this;
    }

    /**
     * Get bottom decoration data
     *
     * @return array
     */
    public function getBottomDecoration()
    {
        if ($this->hasDijitParam('bottomDecoration')) {
            return $this->getDijitParam('bottomDecoration');
        }
        return array();
    }

    /**
     * Set dijit to use with bottom decoration
     *
     * @param mixed $dijit
     * @return Zend_Dojo_Form_Element_HorizontalSlider
     */
    public function setBottomDecorationDijit($dijit)
    {
        $decoration = $this->getBottomDecoration();
        $decoration['dijit'] = (string) $dijit;
        $this->setDijitParam('bottomDecoration', $decoration);
        return $this;
    }

    /**
     * Set container to use with bottom decoration
     *
     * @param mixed $container
     * @return Zend_Dojo_Form_Element_HorizontalSlider
     */
    public function setBottomDecorationContainer($container)
    {
        $decoration = $this->getBottomDecoration();
        $decoration['container'] = (string) $container;
        $this->setDijitParam('bottomDecoration', $decoration);
        return $this;
    }

    /**
     * Set labels to use with bottom decoration
     *
     * @param  array $labels
     * @return Zend_Dojo_Form_Element_HorizontalSlider
     */
    public function setBottomDecorationLabels(array $labels)
    {
        $decoration = $this->getBottomDecoration();
        $decoration['labels'] = array_values($labels);
        $this->setDijitParam('bottomDecoration', $decoration);
        return $this;
    }

    /**
     * Set params to use with bottom decoration
     *
     * @param  array $params
     * @return Zend_Dojo_Form_Element_HorizontalSlider
     */
    public function setBottomDecorationParams(array $params)
    {
        $decoration = $this->getBottomDecoration();
        $decoration['params'] = $params;
        $this->setDijitParam('bottomDecoration', $decoration);
        return $this;
    }

    /**
     * Set attribs to use with bottom decoration
     *
     * @param  array $attribs
     * @return Zend_Dojo_Form_Element_HorizontalSlider
     */
    public function setBottomDecorationAttribs(array $attribs)
    {
        $decoration = $this->getBottomDecoration();
        $decoration['attribs'] = $attribs;
        $this->setDijitParam('bottomDecoration', $decoration);
        return $this;
    }
}
