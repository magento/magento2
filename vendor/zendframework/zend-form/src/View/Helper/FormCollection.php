<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\View\Helper;

use RuntimeException;
use Zend\Form\Element;
use Zend\Form\ElementInterface;
use Zend\Form\Element\Collection as CollectionElement;
use Zend\Form\FieldsetInterface;
use Zend\Form\LabelAwareInterface;
use Zend\View\Helper\AbstractHelper as BaseAbstractHelper;

class FormCollection extends AbstractHelper
{
    /**
     * If set to true, collections are automatically wrapped around a fieldset
     *
     * @var bool
     */
    protected $shouldWrap = true;

    /**
     * This is the default wrapper that the collection is wrapped into
     *
     * @var string
     */
    protected $wrapper = '<fieldset%4$s>%2$s%1$s%3$s</fieldset>';

    /**
     * This is the default label-wrapper
     *
     * @var string
     */
    protected $labelWrapper = '<legend>%s</legend>';

    /**
     * Where shall the template-data be inserted into
     *
     * @var string
     */
    protected $templateWrapper = '<span data-template="%s"></span>';

    /**
     * The name of the default view helper that is used to render sub elements.
     *
     * @var string
     */
    protected $defaultElementHelper = 'formrow';

    /**
     * The view helper used to render sub elements.
     *
     * @var AbstractHelper
     */
    protected $elementHelper;

    /**
     * The view helper used to render sub fieldsets.
     *
     * @var AbstractHelper
     */
    protected $fieldsetHelper;

    /**
     * Invoke helper as function
     *
     * Proxies to {@link render()}.
     *
     * @param  ElementInterface|null $element
     * @param  bool                  $wrap
     * @return string|FormCollection
     */
    public function __invoke(ElementInterface $element = null, $wrap = true)
    {
        if (!$element) {
            return $this;
        }

        $this->setShouldWrap($wrap);

        return $this->render($element);
    }

    /**
     * Render a collection by iterating through all fieldsets and elements
     *
     * @param  ElementInterface $element
     * @return string
     */
    public function render(ElementInterface $element)
    {
        $renderer = $this->getView();
        if (!method_exists($renderer, 'plugin')) {
            // Bail early if renderer is not pluggable
            return '';
        }

        $markup           = '';
        $templateMarkup   = '';
        $elementHelper    = $this->getElementHelper();
        $fieldsetHelper   = $this->getFieldsetHelper();

        if ($element instanceof CollectionElement && $element->shouldCreateTemplate()) {
            $templateMarkup = $this->renderTemplate($element);
        }

        foreach ($element->getIterator() as $elementOrFieldset) {
            if ($elementOrFieldset instanceof FieldsetInterface) {
                $markup .= $fieldsetHelper($elementOrFieldset, $this->shouldWrap());
            } elseif ($elementOrFieldset instanceof ElementInterface) {
                $markup .= $elementHelper($elementOrFieldset);
            }
        }

        // Every collection is wrapped by a fieldset if needed
        if ($this->shouldWrap) {
            $attributes = $element->getAttributes();
            unset($attributes['name']);
            $attributesString = count($attributes) ? ' ' . $this->createAttributesString($attributes) : '';

            $label = $element->getLabel();
            $legend = '';

            if (!empty($label)) {
                if (null !== ($translator = $this->getTranslator())) {
                    $label = $translator->translate(
                        $label,
                        $this->getTranslatorTextDomain()
                    );
                }

                if (! $element instanceof LabelAwareInterface || ! $element->getLabelOption('disable_html_escape')) {
                    $escapeHtmlHelper = $this->getEscapeHtmlHelper();
                    $label = $escapeHtmlHelper($label);
                }

                $legend = sprintf(
                    $this->labelWrapper,
                    $label
                );
            }

            $markup = sprintf(
                $this->wrapper,
                $markup,
                $legend,
                $templateMarkup,
                $attributesString
            );
        } else {
            $markup .= $templateMarkup;
        }

        return $markup;
    }

    /**
     * Only render a template
     *
     * @param  CollectionElement $collection
     * @return string
     */
    public function renderTemplate(CollectionElement $collection)
    {
        $elementHelper          = $this->getElementHelper();
        $escapeHtmlAttribHelper = $this->getEscapeHtmlAttrHelper();
        $fieldsetHelper         = $this->getFieldsetHelper();

        $templateMarkup         = '';

        $elementOrFieldset = $collection->getTemplateElement();

        if ($elementOrFieldset instanceof FieldsetInterface) {
            $templateMarkup .= $fieldsetHelper($elementOrFieldset, $this->shouldWrap());
        } elseif ($elementOrFieldset instanceof ElementInterface) {
            $templateMarkup .= $elementHelper($elementOrFieldset);
        }

        return sprintf(
            $this->getTemplateWrapper(),
            $escapeHtmlAttribHelper($templateMarkup)
        );
    }

    /**
     * If set to true, collections are automatically wrapped around a fieldset
     *
     * @param  bool $wrap
     * @return FormCollection
     */
    public function setShouldWrap($wrap)
    {
        $this->shouldWrap = (bool) $wrap;
        return $this;
    }

    /**
     * Get wrapped
     *
     * @return bool
     */
    public function shouldWrap()
    {
        return $this->shouldWrap;
    }

    /**
     * Sets the name of the view helper that should be used to render sub elements.
     *
     * @param  string $defaultSubHelper The name of the view helper to set.
     * @return FormCollection
     */
    public function setDefaultElementHelper($defaultSubHelper)
    {
        $this->defaultElementHelper = $defaultSubHelper;
        return $this;
    }

    /**
     * Gets the name of the view helper that should be used to render sub elements.
     *
     * @return string
     */
    public function getDefaultElementHelper()
    {
        return $this->defaultElementHelper;
    }

    /**
     * Sets the element helper that should be used by this collection.
     *
     * @param  AbstractHelper $elementHelper The element helper to use.
     * @return FormCollection
     */
    public function setElementHelper(AbstractHelper $elementHelper)
    {
        $this->elementHelper = $elementHelper;
        return $this;
    }

    /**
     * Retrieve the element helper.
     *
     * @return AbstractHelper
     * @throws RuntimeException
     */
    protected function getElementHelper()
    {
        if ($this->elementHelper) {
            return $this->elementHelper;
        }

        if (method_exists($this->view, 'plugin')) {
            $this->elementHelper = $this->view->plugin($this->getDefaultElementHelper());
        }

        if (!$this->elementHelper instanceof BaseAbstractHelper) {
            // @todo Ideally the helper should implement an interface.
            throw new RuntimeException('Invalid element helper set in FormCollection. The helper must be an instance of AbstractHelper.');
        }

        return $this->elementHelper;
    }

    /**
     * Sets the fieldset helper that should be used by this collection.
     *
     * @param  AbstractHelper $fieldsetHelper The fieldset helper to use.
     * @return FormCollection
     */
    public function setFieldsetHelper(AbstractHelper $fieldsetHelper)
    {
        $this->fieldsetHelper = $fieldsetHelper;
        return $this;
    }

    /**
     * Retrieve the fieldset helper.
     *
     * @return FormCollection
     */
    protected function getFieldsetHelper()
    {
        if ($this->fieldsetHelper) {
            return $this->fieldsetHelper;
        }

        return $this;
    }

    /**
     * Get the wrapper for the collection
     *
     * @return string
     */
    public function getWrapper()
    {
        return $this->wrapper;
    }

    /**
     * Set the wrapper for this collection
     *
     * The string given will be passed through sprintf with the following three
     * replacements:
     *
     * 1. The content of the collection
     * 2. The label of the collection. If no label is given this will be an empty
     *   string
     * 3. The template span-tag. This might also be an empty string
     *
     * The preset default is <pre><fieldset>%2$s%1$s%3$s</fieldset></pre>
     *
     * @param string $wrapper
     *
     * @return self
     */
    public function setWrapper($wrapper)
    {
        $this->wrapper = $wrapper;

        return $this;
    }

    /**
     * Set the label-wrapper
     * The string will be passed through sprintf with the label as single
     * parameter
     * This defaults to '<legend>%s</legend>'
     *
     * @param string $labelWrapper
     *
     * @return self
     */
    public function setLabelWrapper($labelWrapper)
    {
        $this->labelWrapper = $labelWrapper;

        return $this;
    }

    /**
     * Get the wrapper for the label
     *
     * @return string
     */
    public function getLabelWrapper()
    {
        return $this->labelWrapper;
    }

    /**
     * Ge the wrapper for the template
     *
     * @return string
     */
    public function getTemplateWrapper()
    {
        return $this->templateWrapper;
    }

    /**
     * Set the string where the template will be inserted into
     *
     * This string will be passed through sprintf and has the template as single
     * parameter
     *
     * THis defaults to '<span data-template="%s"></span>'
     *
     * @param string $templateWrapper
     *
     * @return self
     */
    public function setTemplateWrapper($templateWrapper)
    {
        $this->templateWrapper = $templateWrapper;

        return $this;
    }
}
