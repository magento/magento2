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
 * Data form abstract class
 *
 * @category   Varien
 * @package    Varien_Data
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Varien_Data_Form_Element_Abstract extends Varien_Data_Form_Abstract
{
    protected $_id;
    protected $_type;
    /** @var Varien_Data_Form */
    protected $_form;
    protected $_elements;
    protected $_renderer;

    /**
     * Shows whether current element belongs to Basic or Advanced form layout
     *
     * @var bool
     */
    protected $_advanced = false;

    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->_renderer = Varien_Data_Form::getElementRenderer();
    }

    /**
     * Add form element
     *
     * @param   Varien_Data_Form_Element_Abstract $element
     * @return  Varien_Data_Form
     */
    public function addElement(Varien_Data_Form_Element_Abstract $element, $after=false)
    {
        if ($this->getForm()) {
            $this->getForm()->checkElementId($element->getId());
            $this->getForm()->addElementToCollection($element);
        }

        parent::addElement($element, $after);
        return $this;
    }

    /**
     * Shows whether current element belongs to Basic or Advanced form layout
     *
     * @return  bool
     */
    public function isAdvanced() {
        return $this->_advanced;
    }

    /**
     * Set _advanced layout property
     *
     * @param bool $advanced
     * @return Varien_Data_Form_Element_Abstract
     */
    public function setAdvanced($advanced) {
        $this->_advanced = $advanced;
        return $this;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getType()
    {
        return $this->_type;
    }

    /**
     * Get form
     *
     * @return Varien_Data_Form
     */
    public function getForm()
    {
        return $this->_form;
    }

    public function setId($id)
    {
        $this->_id = $id;
        $this->setData('html_id', $id);
        return $this;
    }

    public function getHtmlId()
    {
        return $this->getForm()->getHtmlIdPrefix() . $this->getData('html_id') . $this->getForm()->getHtmlIdSuffix();
    }

    public function getName()
    {
        $name = $this->getData('name');
        if ($suffix = $this->getForm()->getFieldNameSuffix()) {
            $name = $this->getForm()->addSuffixToName($name, $suffix);
        }
        return $name;
    }

    public function setType($type)
    {
        $this->_type = $type;
        $this->setData('type', $type);
        return $this;
    }

    public function setForm($form)
    {
        $this->_form = $form;
        return $this;
    }

    public function removeField($elementId)
    {
        $this->getForm()->removeField($elementId);
        return parent::removeField($elementId);
    }

    public function getHtmlAttributes()
    {
        return array('type', 'title', 'class', 'style', 'onclick', 'onchange', 'disabled', 'readonly', 'tabindex');
    }

    public function addClass($class)
    {
        $oldClass = $this->getClass();
        $this->setClass($oldClass.' '.$class);
        return $this;
    }

    /**
     * Remove CSS class
     *
     * @param string $class
     * @return Varien_Data_Form_Element_Abstract
     */
    public function removeClass($class)
    {
        $classes = array_unique(explode(' ', $this->getClass()));
        if (false !== ($key = array_search($class, $classes))) {
            unset($classes[$key]);
        }
        $this->setClass(implode(' ', $classes));
        return $this;
    }

    protected function _escape($string)
    {
        return htmlspecialchars($string, ENT_COMPAT);
    }

    public function getEscapedValue($index=null)
    {
        $value = $this->getValue($index);

        if ($filter = $this->getValueFilter()) {
            $value = $filter->filter($value);
        }
        return $this->_escape($value);
    }

    public function setRenderer(Varien_Data_Form_Element_Renderer_Interface $renderer)
    {
        $this->_renderer = $renderer;
        return $this;
    }

    public function getRenderer()
    {
        return $this->_renderer;
    }

    protected function _getUiId($suffix = null)
    {
        if ($this->_renderer instanceof Mage_Core_Block_Abstract) {
            return $this->_renderer->getUiId($this->getType(), $this->getName(), $suffix);
        } else {
            return ' data-ui-id="form-element-' . $this->getName() . ($suffix ? : '') . '"';
        }
    }

    public function getElementHtml()
    {

        $html = '<input id="' . $this->getHtmlId() . '" name="' . $this->getName() . '" '
            . $this->_getUiId()
            . ' value="' . $this->getEscapedValue() . '" ' . $this->serialize($this->getHtmlAttributes()) . '/>' . "\n";
        $html.= $this->getAfterElementHtml();
        return $html;
    }

    public function getAfterElementHtml()
    {
        return $this->getData('after_element_html');
    }

    /**
     * Render HTML for element's label
     *
     * @param string $idSuffix
     * @return string
     */
    public function getLabelHtml($idSuffix = '')
    {
        if ($this->getLabel() !== null) {
            $html = '<label for="' . $this->getHtmlId() . $idSuffix . '"' . $this->_getUiId('label') . '>'
                . $this->_escape($this->getLabel())
                . ($this->getRequired() ? ' <span class="required">*</span>' : '') . '</label>' . "\n";
        } else {
            $html = '';
        }
        return $html;
    }

    public function getDefaultHtml()
    {
        $html = $this->getData('default_html');
        if ($html === null) {
            $html = ( $this->getNoSpan() === true ) ? '' : '<span class="field-row">'."\n";
            $html.= $this->getLabelHtml();
            $html.= $this->getElementHtml();
            $html.= ( $this->getNoSpan() === true ) ? '' : '</span>'."\n";
        }
        return $html;
    }

    public function getHtml()
    {
        if ($this->getRequired()) {
            $this->addClass('required-entry');
        }
        if ($this->_renderer) {
            $html = $this->_renderer->render($this);
        } else {
            $html = $this->getDefaultHtml();
        }
        return $html;
    }

    public function toHtml()
    {
        return $this->getHtml();
    }

    public function serialize($attributes = array(), $valueSeparator='=', $fieldSeparator=' ', $quote='"')
    {
        if (in_array('disabled', $attributes) && !empty($this->_data['disabled'])) {
            $this->_data['disabled'] = 'disabled';
        } else {
            unset($this->_data['disabled']);
        }
        if (in_array('checked', $attributes) && !empty($this->_data['checked'])) {
            $this->_data['checked'] = 'checked';
        } else {
            unset($this->_data['checked']);
        }
        return parent::serialize($attributes, $valueSeparator, $fieldSeparator, $quote);
    }

    public function getReadonly()
    {
        if ($this->hasData('readonly_disabled')) {
            return $this->_getData('readonly_disabled');
        }

        return $this->_getData('readonly');
    }

    public function getHtmlContainerId()
    {
        if ($this->hasData('container_id')) {
            return $this->getData('container_id');
        } elseif ($idPrefix = $this->getForm()->getFieldContainerIdPrefix()) {
            return $idPrefix . $this->getId();
        }
        return '';
    }

    /**
     * Add specified values to element values
     *
     * @param string|int|array $values
     * @param bool $overwrite
     * @return Varien_Data_Form_Element_Abstract
     */
    public function addElementValues($values, $overwrite = false)
    {
        if (empty($values) || (is_string($values) && trim($values) == '')) {
            return $this;
        }
        if (!is_array($values)) {
            $values = Mage::helper('Mage_Core_Helper_Data')->escapeHtml(trim($values));
            $values = array($values => $values);
        }
        $elementValues = $this->getValues();
        if (!empty($elementValues)) {
            foreach ($values as $key => $value) {
                if ((isset($elementValues[$key]) && $overwrite) || !isset($elementValues[$key])) {
                    $elementValues[$key] = Mage::helper('Mage_Core_Helper_Data')->escapeHtml($value);
                }
            }
            $values = $elementValues;
        }
        $this->setValues($values);

        return $this;
    }
}
