<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\AbstractForm;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Escaper;

/**
 * Data form abstract class
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 2.0.0
 */
abstract class AbstractElement extends AbstractForm
{
    /**
     * @var string|int
     * @since 2.0.0
     */
    protected $_id;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_type;

    /**
     * @var Form
     * @since 2.0.0
     */
    protected $_form;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $_elements;

    /**
     * @var RendererInterface
     * @since 2.0.0
     */
    protected $_renderer;

    /**
     * Shows whether current element belongs to Basic or Advanced form layout
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_advanced = false;

    /**
     * @var Escaper
     * @since 2.0.0
     */
    protected $_escaper;

    /**
     * Lock html attribute
     *
     * @var string
     * @since 2.0.0
     */
    private $lockHtmlAttribute = 'data-locked';

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = []
    ) {
        $this->_escaper = $escaper;
        parent::__construct($factoryElement, $factoryCollection, $data);
        $this->_renderer = \Magento\Framework\Data\Form::getElementRenderer();
    }

    /**
     * Add form element
     *
     * @param AbstractElement $element
     * @param bool $after
     * @return Form
     * @since 2.0.0
     */
    public function addElement(AbstractElement $element, $after = false)
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
     * @return bool
     * @since 2.0.0
     */
    public function isAdvanced()
    {
        return $this->_advanced;
    }

    /**
     * Set _advanced layout property
     *
     * @param bool $advanced
     * @return $this
     * @since 2.0.0
     */
    public function setAdvanced($advanced)
    {
        $this->_advanced = $advanced;
        return $this;
    }

    /**
     * Get id.
     *
     * @return string|int
     * @since 2.0.0
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Get type.
     *
     * @return string
     * @since 2.0.0
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Get form
     *
     * @return Form
     * @since 2.0.0
     */
    public function getForm()
    {
        return $this->_form;
    }

    /**
     * Set the Id.
     *
     * @param string|int $id
     * @return $this
     * @since 2.0.0
     */
    public function setId($id)
    {
        $this->_id = $id;
        $this->setData('html_id', $id);
        return $this;
    }

    /**
     * Get the Html Id.
     *
     * @return string
     * @since 2.0.0
     */
    public function getHtmlId()
    {
        return $this->getForm()->getHtmlIdPrefix() . $this->getData('html_id') . $this->getForm()->getHtmlIdSuffix();
    }

    /**
     * Get the name.
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getName()
    {
        $name = $this->getData('name');
        if ($suffix = $this->getForm()->getFieldNameSuffix()) {
            $name = $this->getForm()->addSuffixToName($name, $suffix);
        }
        return $name;
    }

    /**
     * Set the type.
     *
     * @param string $type
     * @return $this
     * @since 2.0.0
     */
    public function setType($type)
    {
        $this->_type = $type;
        $this->setData('type', $type);
        return $this;
    }

    /**
     * @param AbstractForm $form
     * @return $this
     * @since 2.0.0
     */
    public function setForm($form)
    {
        $this->_form = $form;
        return $this;
    }

    /**
     * Remove field
     *
     * @param string $elementId
     * @return AbstractForm
     * @since 2.0.0
     */
    public function removeField($elementId)
    {
        $this->getForm()->removeField($elementId);
        return parent::removeField($elementId);
    }

    /**
     * Return the attributes for Html.
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getHtmlAttributes()
    {
        return [
            'type',
            'title',
            'class',
            'style',
            'onclick',
            'onchange',
            'disabled',
            'readonly',
            'tabindex',
            'placeholder',
            'data-form-part',
            'data-role',
            'data-action',
            'checked',
        ];
    }

    /**
     * Add a class.
     *
     * @param string $class
     * @return $this
     * @since 2.0.0
     */
    public function addClass($class)
    {
        $oldClass = $this->getClass();
        $this->setClass($oldClass . ' ' . $class);
        return $this;
    }

    /**
     * Remove CSS class
     *
     * @param string $class
     * @return $this
     * @since 2.0.0
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

    /**
     * Escape a string's contents.
     *
     * @param string $string
     * @return string
     * @since 2.0.0
     */
    protected function _escape($string)
    {
        return htmlspecialchars($string, ENT_COMPAT);
    }

    /**
     * Return the escaped value of the element specified by the given index.
     *
     * @param null|int|string $index
     * @return string
     * @since 2.0.0
     */
    public function getEscapedValue($index = null)
    {
        $value = $this->getValue($index);

        if ($filter = $this->getValueFilter()) {
            $value = $filter->filter($value);
        }
        return $this->_escape($value);
    }

    /**
     * Set the renderer.
     *
     * @param RendererInterface $renderer
     * @return $this
     * @since 2.0.0
     */
    public function setRenderer(RendererInterface $renderer)
    {
        $this->_renderer = $renderer;
        return $this;
    }

    /**
     * Get the renderer.
     *
     * @return RendererInterface
     * @since 2.0.0
     */
    public function getRenderer()
    {
        return $this->_renderer;
    }

    /**
     * @param null|string $suffix
     * @return string
     * @since 2.0.0
     */
    protected function _getUiId($suffix = null)
    {
        if ($this->_renderer instanceof \Magento\Framework\View\Element\AbstractBlock) {
            return $this->_renderer->getUiId($this->getType(), $this->getName(), $suffix);
        } else {
            return ' data-ui-id="form-element-' . $this->getName() . ($suffix ?: '') . '"';
        }
    }

    /**
     * Get the Html for the element.
     *
     * @return string
     * @since 2.0.0
     */
    public function getElementHtml()
    {
        $html = '';
        $htmlId = $this->getHtmlId();

        $beforeElementHtml = $this->getBeforeElementHtml();
        if ($beforeElementHtml) {
            $html .= '<label class="addbefore" for="' . $htmlId . '">' . $beforeElementHtml . '</label>';
        }

        $html .= '<input id="' . $htmlId . '" name="' . $this->getName() . '" ' . $this->_getUiId() . ' value="' .
            $this->getEscapedValue() . '" ' . $this->serialize($this->getHtmlAttributes()) . '/>';

        $afterElementJs = $this->getAfterElementJs();
        if ($afterElementJs) {
            $html .= $afterElementJs;
        }

        $afterElementHtml = $this->getAfterElementHtml();
        if ($afterElementHtml) {
            $html .= '<label class="addafter" for="' . $htmlId . '">' . $afterElementHtml . '</label>';
        }

        return $html;
    }

    /**
     * Get the before element html.
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getBeforeElementHtml()
    {
        return $this->getData('before_element_html');
    }

    /**
     * Get the after element html.
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getAfterElementHtml()
    {
        return $this->getData('after_element_html');
    }

    /**
     * Get the after element Javascript.
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getAfterElementJs()
    {
        return $this->getData('after_element_js');
    }

    /**
     * Render HTML for element's label
     *
     * @param string $idSuffix
     * @param string $scopeLabel
     * @return string
     * @since 2.0.0
     */
    public function getLabelHtml($idSuffix = '', $scopeLabel = '')
    {
        $scopeLabel = $scopeLabel ? ' data-config-scope="' . $scopeLabel . '"' : '';

        if ($this->getLabel() !== null) {
            $html = '<label class="label admin__field-label" for="' .
                $this->getHtmlId() . $idSuffix . '"' . $this->_getUiId(
                    'label'
                ) . '><span' . $scopeLabel . '>' . $this->_escape(
                    $this->getLabel()
                ) . '</span></label>' . "\n";
        } else {
            $html = '';
        }
        return $html;
    }

    /**
     * Get the default html.
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getDefaultHtml()
    {
        $html = $this->getData('default_html');
        if ($html === null) {
            $html = $this->getNoSpan() === true ? '' : '<div class="admin__field">' . "\n";
            $html .= $this->getLabelHtml();
            $html .= $this->getElementHtml();
            $html .= $this->getNoSpan() === true ? '' : '</div>' . "\n";
        }
        return $html;
    }

    /**
     * Get the html.
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getHtml()
    {
        if ($this->getRequired()) {
            $this->addClass('required-entry _required');
        }
        if ($this->_renderer) {
            $html = $this->_renderer->render($this);
        } else {
            $html = $this->getDefaultHtml();
        }
        return $html;
    }

    /**
     * Get the html.
     *
     * @return mixed
     * @since 2.0.0
     */
    public function toHtml()
    {
        return $this->getHtml();
    }

    /**
     * Serialize the element.
     *
     * @param string[] $attributes
     * @param string $valueSeparator
     * @param string $fieldSeparator
     * @param string $quote
     * @return string
     * @since 2.0.0
     */
    public function serialize($attributes = [], $valueSeparator = '=', $fieldSeparator = ' ', $quote = '"')
    {
        if ($this->isLocked() && !empty($attributes)) {
            $attributes[] = $this->lockHtmlAttribute;
        }
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

    /**
     * Indicates the elements readonly status.
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getReadonly()
    {
        if ($this->hasData('readonly_disabled')) {
            return $this->_getData('readonly_disabled');
        }

        return $this->_getData('readonly');
    }

    /**
     * Get the container Id.
     *
     * @return mixed
     * @since 2.0.0
     */
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
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    public function addElementValues($values, $overwrite = false)
    {
        if (empty($values) || is_string($values) && trim($values) == '') {
            return $this;
        }
        if (!is_array($values)) {
            $values = $this->_escaper->escapeHtml(trim($values));
            $values = [$values => $values];
        }
        $elementValues = $this->getValues();
        if (!empty($elementValues)) {
            foreach ($values as $key => $value) {
                if (isset($elementValues[$key]) && $overwrite || !isset($elementValues[$key])) {
                    $elementValues[$key] = $this->_escaper->escapeHtml($value);
                }
            }
            $values = $elementValues;
        }
        $this->setValues($values);

        return $this;
    }

    /**
     * Lock element
     *
     * @return void
     * @since 2.0.0
     */
    public function lock()
    {
        $this->setData($this->lockHtmlAttribute, 1);
    }

    /**
     * Is element locked
     *
     * @return bool
     * @since 2.0.0
     */
    public function isLocked()
    {
        return $this->getData($this->lockHtmlAttribute) == 1;
    }
}
