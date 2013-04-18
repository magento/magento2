<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Varien
 * @package    Varien_Data
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Form fieldset
 *
 * @category   Varien
 * @package    Varien_Data
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Varien_Data_Form_Element_Fieldset extends Varien_Data_Form_Element_Abstract
{
    /**
     * Sort child elements by specified data key
     *
     * @var string
     */
    protected $_sortChildrenByKey = '';

    /**
     * Children sort direction
     *
     * @var int
     */
    protected $_sortChildrenDirection = SORT_ASC;

    /**
     * Label for Advanced section
     *
     * @var string
     */
    protected $_labelAdvanceSection = '';

    /**
     * Enter description here...
     *
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->_renderer = Varien_Data_Form::getFieldsetRenderer();
        $this->setType('fieldset');
        if (isset($attributes['advancedSection'])) {
            $this->_labelAdvanceSection = $attributes['advancedSection'];
        } else {
            $this->_labelAdvanceSection = Mage::helper('Mage_Core_Helper_Data')->__('Additional Settings');
        }
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = '<fieldset id="'.$this->getHtmlId().'"'.$this->serialize(array('class')) . $this->_getUiId() . '>'."\n";
        if ($this->getLegend()) {
            $html.= '<legend ' . $this->_getUiId('legend') . '>'.$this->getLegend().'</legend>'."\n";
        }
        $html.= $this->getChildrenHtml();
        $html.= '</fieldset>'."\n";
        $html.= $this->getAfterElementHtml();
        return $html;
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getChildrenHtml()
    {
        $html = '';
        foreach ($this->getSortedElements() as $element) {
            if ($element->getType() != 'fieldset') {
                $html.= $element->toHtml();
            }
        }
        return $html;
    }

    /**
     * Get Basic elements' html in sorted order
     *
     * @return string
     */
    public function getBasicChildrenHtml()
    {
        $html = '';
        foreach ($this->getSortedElements() as $element) {
            if (!$element->isAdvanced()) {
                $html.= $element->toHtml();
            }
        }
        return $html;
    }

    /**
     * Get Number of Bacic Children
     *
     * @return int
     */
    public function getCountBasicChildren()
    {
        $count = 0;
        foreach ($this->getElements() as $element) {
            if (!$element->isAdvanced()) {
                $count += 1;
            }
        }
        return $count;
    }

    /**
     * Get Advanced elements' html in sorted order
     *
     * @return string
     */
    public function getAdvancedChildrenHtml()
    {
        $html = '';
        foreach ($this->getSortedElements() as $element) {
            if ($element->isAdvanced()) {
                $html.= $element->toHtml();
            }
        }
        return $html;
    }

    /**
     * Whether fieldset contains advance section
     *
     * @return bool
     */
    public function hasAdvanced()
    {
        foreach ($this->getElements() as $element) {
            if ($element->isAdvanced()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Set advanced label
     *
     * @param string $labelAdvanced
     * @return Varien_Data_Form_Element_Fieldset
     */
    public function setAdvancedLabel($labelAdvanced)
    {
        $this->_labelAdvanceSection = $labelAdvanced;
        return $this;
    }

    /**
     * Get advanced label
     *
     * @return string
     */
    public function getAdvancedLabel()
    {
        return $this->_labelAdvanceSection;
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getSubFieldsetHtml()
    {
        $html = '';
        foreach ($this->getSortedElements() as $element) {
            if ($element->getType() == 'fieldset' && !$element->isAdvanced()) {
                $html.= $element->toHtml();
            }
        }
        return $html;
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getDefaultHtml()
    {
        $html = '<div><h4 class="icon-head head-edit-form fieldset-legend">'.$this->getLegend().'</h4>'."\n";
        $html.= $this->getElementHtml();
        $html.= '</div>';
        return $html;
    }

    /**
     * Enter description here...
     *
     * @param string $elementId
     * @param string $type
     * @param array $config
     * @param boolean $after
     * @return Varien_Data_Form_Element_Abstract
     */
    public function addField($elementId, $type, $config, $after = false, $isAdvanced = false)
    {
        $element = parent::addField($elementId, $type, $config, $after);
        if ($renderer = Varien_Data_Form::getFieldsetElementRenderer()) {
            $element->setRenderer($renderer);
        }
        $element->setAdvanced($isAdvanced);
        return $element;
    }

    /**
     * Commence sorting elements by values by specified data key
     *
     * @param string $key
     * @param int $direction
     * @return Varien_Data_Form_Element_Fieldset
     */
    public function setSortElementsByAttribute($key, $direction = SORT_ASC)
    {
        $this->_sortChildrenByKey = $key;
        $this->_sortDirection = $direction;
        return $this;
    }

    /**
     * Get sorted elements as array
     *
     * @return array
     */
    public function getSortedElements()
    {
        $elements = array();
        // sort children by value by specified key
        if ($this->_sortChildrenByKey) {
            $sortKey = $this->_sortChildrenByKey;
            $uniqueIncrement = 0; // in case if there are elements with same values
            foreach ($this->getElements() as $e) {
                $key = '_' . $uniqueIncrement;
                if ($e->hasData($sortKey)) {
                    $key = $e->getDataUsingMethod($sortKey) . $key;
                }
                $elements[$key] = $e;
                $uniqueIncrement++;
            }
            ksort($elements, $this->_sortChildrenDirection);
            $elements = array_values($elements);
        } else {
            foreach ($this->getElements() as $element) {
                $elements[] = $element;
            }
        }
        return $elements;
    }
}
