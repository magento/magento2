<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\Element\MultiCheckbox as MultiCheckboxElement;
use Zend\Form\Exception;
use Zend\Form\LabelAwareInterface;

class FormMultiCheckbox extends FormInput
{
    const LABEL_APPEND  = 'append';
    const LABEL_PREPEND = 'prepend';

    /**
     * The attributes applied to option label
     *
     * @var array
     */
    protected $labelAttributes;

    /**
     * Where will be label rendered?
     *
     * @var string
     */
    protected $labelPosition = self::LABEL_APPEND;

    /**
     * Separator for checkbox elements
     *
     * @var string
     */
    protected $separator = '';

    /**
     * Prefixing the element with a hidden element for the unset value?
     *
     * @var bool
     */
    protected $useHiddenElement = false;

    /**
     * The unchecked value used when "UseHiddenElement" is turned on
     *
     * @var string
     */
    protected $uncheckedValue = '';

    /**
     * Form input helper instance
     *
     * @var FormInput
     */
    protected $inputHelper;

    /**
     * Form label helper instance
     *
     * @var FormLabel
     */
    protected $labelHelper;

    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()}.
     *
     * @param  ElementInterface|null $element
     * @param  null|string           $labelPosition
     * @return string|FormMultiCheckbox
     */
    public function __invoke(ElementInterface $element = null, $labelPosition = null)
    {
        if (!$element) {
            return $this;
        }

        if ($labelPosition !== null) {
            $this->setLabelPosition($labelPosition);
        }

        return $this->render($element);
    }

    /**
     * Render a form <input> element from the provided $element
     *
     * @param  ElementInterface $element
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    public function render(ElementInterface $element)
    {
        if (!$element instanceof MultiCheckboxElement) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s requires that the element is of type Zend\Form\Element\MultiCheckbox',
                __METHOD__
            ));
        }

        $name = static::getName($element);

        $options = $element->getValueOptions();

        $attributes         = $element->getAttributes();
        $attributes['name'] = $name;
        $attributes['type'] = $this->getInputType();
        $selectedOptions    = (array) $element->getValue();

        $rendered = $this->renderOptions($element, $options, $selectedOptions, $attributes);

        // Render hidden element
        $useHiddenElement = method_exists($element, 'useHiddenElement') && $element->useHiddenElement()
            ? $element->useHiddenElement()
            : $this->useHiddenElement;

        if ($useHiddenElement) {
            $rendered = $this->renderHiddenElement($element, $attributes) . $rendered;
        }

        return $rendered;
    }

    /**
     * Render options
     *
     * @param  MultiCheckboxElement $element
     * @param  array                $options
     * @param  array                $selectedOptions
     * @param  array                $attributes
     * @return string
     */
    protected function renderOptions(MultiCheckboxElement $element, array $options, array $selectedOptions, array $attributes)
    {
        $escapeHtmlHelper = $this->getEscapeHtmlHelper();
        $labelHelper      = $this->getLabelHelper();
        $labelClose       = $labelHelper->closeTag();
        $labelPosition    = $this->getLabelPosition();
        $globalLabelAttributes = array();
        $closingBracket   = $this->getInlineClosingBracket();

        if ($element instanceof LabelAwareInterface) {
            $globalLabelAttributes = $element->getLabelAttributes();
        }

        if (empty($globalLabelAttributes)) {
            $globalLabelAttributes = $this->labelAttributes;
        }

        $combinedMarkup = array();
        $count          = 0;

        foreach ($options as $key => $optionSpec) {
            $count++;
            if ($count > 1 && array_key_exists('id', $attributes)) {
                unset($attributes['id']);
            }

            $value           = '';
            $label           = '';
            $inputAttributes = $attributes;
            $labelAttributes = $globalLabelAttributes;
            $selected        = (isset($inputAttributes['selected']) && $inputAttributes['type'] != 'radio' && $inputAttributes['selected']);
            $disabled        = (isset($inputAttributes['disabled']) && $inputAttributes['disabled']);

            if (is_scalar($optionSpec)) {
                $optionSpec = array(
                    'label' => $optionSpec,
                    'value' => $key
                );
            }

            if (isset($optionSpec['value'])) {
                $value = $optionSpec['value'];
            }
            if (isset($optionSpec['label'])) {
                $label = $optionSpec['label'];
            }
            if (isset($optionSpec['selected'])) {
                $selected = $optionSpec['selected'];
            }
            if (isset($optionSpec['disabled'])) {
                $disabled = $optionSpec['disabled'];
            }
            if (isset($optionSpec['label_attributes'])) {
                $labelAttributes = (isset($labelAttributes))
                    ? array_merge($labelAttributes, $optionSpec['label_attributes'])
                    : $optionSpec['label_attributes'];
            }
            if (isset($optionSpec['attributes'])) {
                $inputAttributes = array_merge($inputAttributes, $optionSpec['attributes']);
            }

            if (in_array($value, $selectedOptions)) {
                $selected = true;
            }

            $inputAttributes['value']    = $value;
            $inputAttributes['checked']  = $selected;
            $inputAttributes['disabled'] = $disabled;

            $input = sprintf(
                '<input %s%s',
                $this->createAttributesString($inputAttributes),
                $closingBracket
            );

            if (null !== ($translator = $this->getTranslator())) {
                $label = $translator->translate(
                    $label,
                    $this->getTranslatorTextDomain()
                );
            }

            if (! $element instanceof LabelAwareInterface || ! $element->getLabelOption('disable_html_escape')) {
                $label = $escapeHtmlHelper($label);
            }

            $labelOpen = $labelHelper->openTag($labelAttributes);
            $template  = $labelOpen . '%s%s' . $labelClose;
            switch ($labelPosition) {
                case self::LABEL_PREPEND:
                    $markup = sprintf($template, $label, $input);
                    break;
                case self::LABEL_APPEND:
                default:
                    $markup = sprintf($template, $input, $label);
                    break;
            }

            $combinedMarkup[] = $markup;
        }

        return implode($this->getSeparator(), $combinedMarkup);
    }

    /**
     * Render a hidden element for empty/unchecked value
     *
     * @param  MultiCheckboxElement $element
     * @param  array                $attributes
     * @return string
     */
    protected function renderHiddenElement(MultiCheckboxElement $element, array $attributes)
    {
        $closingBracket = $this->getInlineClosingBracket();

        $uncheckedValue = $element->getUncheckedValue()
            ? $element->getUncheckedValue()
            : $this->uncheckedValue;

        $hiddenAttributes = array(
            'name'  => $element->getName(),
            'value' => $uncheckedValue,
        );

        return sprintf(
            '<input type="hidden" %s%s',
            $this->createAttributesString($hiddenAttributes),
            $closingBracket
        );
    }

    /**
     * Sets the attributes applied to option label.
     *
     * @param  array|null $attributes
     * @return FormMultiCheckbox
     */
    public function setLabelAttributes($attributes)
    {
        $this->labelAttributes = $attributes;
        return $this;
    }

    /**
     * Returns the attributes applied to each option label.
     *
     * @return array|null
     */
    public function getLabelAttributes()
    {
        return $this->labelAttributes;
    }

    /**
     * Set value for labelPosition
     *
     * @param  mixed $labelPosition
     * @throws Exception\InvalidArgumentException
     * @return FormMultiCheckbox
     */
    public function setLabelPosition($labelPosition)
    {
        $labelPosition = strtolower($labelPosition);
        if (!in_array($labelPosition, array(self::LABEL_APPEND, self::LABEL_PREPEND))) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects either %s::LABEL_APPEND or %s::LABEL_PREPEND; received "%s"',
                __METHOD__,
                __CLASS__,
                __CLASS__,
                (string) $labelPosition
            ));
        }
        $this->labelPosition = $labelPosition;

        return $this;
    }

    /**
     * Get position of label
     *
     * @return string
     */
    public function getLabelPosition()
    {
        return $this->labelPosition;
    }

    /**
     * Set separator string for checkbox elements
     *
     * @param  string $separator
     * @return FormMultiCheckbox
     */
    public function setSeparator($separator)
    {
        $this->separator = (string) $separator;
        return $this;
    }

    /**
     * Get separator for checkbox elements
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    /**
     * Sets the option for prefixing the element with a hidden element
     * for the unset value.
     *
     * @param  bool $useHiddenElement
     * @return FormMultiCheckbox
     */
    public function setUseHiddenElement($useHiddenElement)
    {
        $this->useHiddenElement = (bool) $useHiddenElement;
        return $this;
    }

    /**
     * Returns the option for prefixing the element with a hidden element
     * for the unset value.
     *
     * @return bool
     */
    public function getUseHiddenElement()
    {
        return $this->useHiddenElement;
    }

    /**
     * Sets the unchecked value used when "UseHiddenElement" is turned on.
     *
     * @param  bool $value
     * @return FormMultiCheckbox
     */
    public function setUncheckedValue($value)
    {
        $this->uncheckedValue = $value;
        return $this;
    }

    /**
     * Returns the unchecked value used when "UseHiddenElement" is turned on.
     *
     * @return string
     */
    public function getUncheckedValue()
    {
        return $this->uncheckedValue;
    }

    /**
     * Return input type
     *
     * @return string
     */
    protected function getInputType()
    {
        return 'checkbox';
    }

    /**
     * Get element name
     *
     * @param  ElementInterface $element
     * @throws Exception\DomainException
     * @return string
     */
    protected static function getName(ElementInterface $element)
    {
        $name = $element->getName();
        if ($name === null || $name === '') {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has an assigned name; none discovered',
                __METHOD__
            ));
        }
        return $name . '[]';
    }

    /**
     * Retrieve the FormInput helper
     *
     * @return FormInput
     */
    protected function getInputHelper()
    {
        if ($this->inputHelper) {
            return $this->inputHelper;
        }

        if (method_exists($this->view, 'plugin')) {
            $this->inputHelper = $this->view->plugin('form_input');
        }

        if (!$this->inputHelper instanceof FormInput) {
            $this->inputHelper = new FormInput();
        }

        return $this->inputHelper;
    }

    /**
     * Retrieve the FormLabel helper
     *
     * @return FormLabel
     */
    protected function getLabelHelper()
    {
        if ($this->labelHelper) {
            return $this->labelHelper;
        }

        if (method_exists($this->view, 'plugin')) {
            $this->labelHelper = $this->view->plugin('form_label');
        }

        if (!$this->labelHelper instanceof FormLabel) {
            $this->labelHelper = new FormLabel();
        }

        return $this->labelHelper;
    }
}
