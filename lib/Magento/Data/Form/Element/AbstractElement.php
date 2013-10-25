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
 * @category   Magento
 * @package    Magento_Data
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Data form abstract class
 *
 * @category   Magento
 * @package    Magento_Data
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Data\Form\Element;

abstract class AbstractElement extends \Magento\Data\Form\AbstractForm
{
    protected $_id;
    protected $_type;
    /** @var \Magento\Data\Form */
    protected $_form;
    protected $_elements;
    protected $_renderer;

    /**
     * Shows whether current element belongs to Basic or Advanced form layout
     *
     * @var bool
     */
    protected $_advanced = false;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Data\Form\Element\CollectionFactory $factoryCollection
     * @param array $attributes
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Data\Form\Element\Factory $factoryElement,
        \Magento\Data\Form\Element\CollectionFactory $factoryCollection,
        $attributes = array()
    ) {
        $this->_coreData = $coreData;
        parent::__construct($factoryElement, $factoryCollection, $attributes);
        $this->_renderer = \Magento\Data\Form::getElementRenderer();
    }

    /**
     * Add form element
     *
     * @param   \Magento\Data\Form\Element\AbstractElement $element
     * @return  \Magento\Data\Form
     */
    public function addElement(\Magento\Data\Form\Element\AbstractElement $element, $after=false)
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
     * @return \Magento\Data\Form\Element\AbstractElement
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
     * @return \Magento\Data\Form
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
        return array('type', 'title', 'class', 'style', 'onclick', 'onchange', 'disabled', 'readonly', 'tabindex', 'placeholder');
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
     * @return \Magento\Data\Form\Element\AbstractElement
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

    public function setRenderer(\Magento\Data\Form\Element\Renderer\RendererInterface $renderer)
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
        if ($this->_renderer instanceof \Magento\Core\Block\AbstractBlock) {
            return $this->_renderer->getUiId($this->getType(), $this->getName(), $suffix);
        } else {
            return ' data-ui-id="form-element-' . $this->getName() . ($suffix ? : '') . '"';
        }
    }

    public function getElementHtml()
    {
        $html = '';
        if ($this->getBeforeElementHtml()) {
            $html .= '<label class="addbefore" for="' . $this->getHtmlId() . '">' . $this->getBeforeElementHtml() . '</label>';            
        }
        $html .= '<input id="' . $this->getHtmlId() . '" name="' . $this->getName() . '" '
            . $this->_getUiId()
            . ' value="' . $this->getEscapedValue() . '" ' . $this->serialize($this->getHtmlAttributes()) . '/>';
        if ($this->getAfterElementHtml()) {
            $html.= '<label class="addafter" for="' . $this->getHtmlId() . '">' . $this->getAfterElementHtml() . '</label>';            
        }
        return $html;
    }

    public function getBeforeElementHtml()
    {
        return $this->getData('before_element_html');
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
        if (!is_null($this->getLabel())) {
            $html = '<label class="label" for="' . $this->getHtmlId() . $idSuffix . '"' . $this->_getUiId('label')
                . '><span>'
                . $this->_escape($this->getLabel())
                . '</span></label>' . "\n";
        } else {
            $html = '';
        }
        return $html;
    }

    public function getDefaultHtml()
    {
        $html = $this->getData('default_html');
        if (is_null($html)) {
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
     * @return \Magento\Data\Form\Element\AbstractElement
     */
    public function addElementValues($values, $overwrite = false)
    {
        if (empty($values) || (is_string($values) && trim($values) == '')) {
            return $this;
        }
        if (!is_array($values)) {
            $values = $this->_coreData->escapeHtml(trim($values));
            $values = array($values => $values);
        }
        $elementValues = $this->getValues();
        if (!empty($elementValues)) {
            foreach ($values as $key => $value) {
                if ((isset($elementValues[$key]) && $overwrite) || !isset($elementValues[$key])) {
                    $elementValues[$key] = $this->_coreData->escapeHtml($value);
                }
            }
            $values = $elementValues;
        }
        $this->setValues($values);

        return $this;
    }
}
