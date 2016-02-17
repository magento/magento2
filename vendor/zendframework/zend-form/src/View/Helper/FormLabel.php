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
use Zend\Form\Exception;
use Zend\Form\LabelAwareInterface;

class FormLabel extends AbstractHelper
{
    const APPEND  = 'append';
    const PREPEND = 'prepend';

    /**
     * Attributes valid for the label tag
     *
     * @var array
     */
    protected $validTagAttributes = array(
        'for'  => true,
        'form' => true,
    );

    /**
     * Generate a form label, optionally with content
     *
     * Always generates a "for" statement, as we cannot assume the form input
     * will be provided in the $labelContent.
     *
     * @param  ElementInterface $element
     * @param  null|string      $labelContent
     * @param  string           $position
     * @throws Exception\DomainException
     * @return string|FormLabel
     */
    public function __invoke(ElementInterface $element = null, $labelContent = null, $position = null)
    {
        if (!$element) {
            return $this;
        }

        $openTag = $this->openTag($element);
        $label   = '';
        if ($labelContent === null || $position !== null) {
            $label = $element->getLabel();
            if (empty($label)) {
                throw new Exception\DomainException(
                    sprintf(
                        '%s expects either label content as the second argument, ' .
                        'or that the element provided has a label attribute; neither found',
                        __METHOD__
                    )
                );
            }

            if (null !== ($translator = $this->getTranslator())) {
                $label = $translator->translate($label, $this->getTranslatorTextDomain());
            }

            if (! $element instanceof LabelAwareInterface || ! $element->getLabelOption('disable_html_escape')) {
                $escapeHtmlHelper = $this->getEscapeHtmlHelper();
                $label = $escapeHtmlHelper($label);
            }
        }

        if ($label && $labelContent) {
            switch ($position) {
                case self::APPEND:
                    $labelContent .= $label;
                    break;
                case self::PREPEND:
                default:
                    $labelContent = $label . $labelContent;
                    break;
            }
        }

        if ($label && null === $labelContent) {
            $labelContent = $label;
        }

        return $openTag . $labelContent . $this->closeTag();
    }

    /**
     * Generate an opening label tag
     *
     * @param  null|array|ElementInterface $attributesOrElement
     * @throws Exception\InvalidArgumentException
     * @throws Exception\DomainException
     * @return string
     */
    public function openTag($attributesOrElement = null)
    {
        if (null === $attributesOrElement) {
            return '<label>';
        }

        if (is_array($attributesOrElement)) {
            $attributes = $this->createAttributesString($attributesOrElement);
            return sprintf('<label %s>', $attributes);
        }

        if (!$attributesOrElement instanceof ElementInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Zend\Form\ElementInterface instance; received "%s"',
                __METHOD__,
                (is_object($attributesOrElement) ? get_class($attributesOrElement) : gettype($attributesOrElement))
            ));
        }

        $id = $this->getId($attributesOrElement);
        if (null === $id) {
            throw new Exception\DomainException(sprintf(
                '%s expects the Element provided to have either a name or an id present; neither found',
                __METHOD__
            ));
        }

        $labelAttributes = array();
        if ($attributesOrElement instanceof LabelAwareInterface) {
            $labelAttributes = $attributesOrElement->getLabelAttributes();
        }

        $attributes = array('for' => $id);

        if (!empty($labelAttributes)) {
            $attributes = array_merge($labelAttributes, $attributes);
        }

        $attributes = $this->createAttributesString($attributes);
        return sprintf('<label %s>', $attributes);
    }

    /**
     * Return a closing label tag
     *
     * @return string
     */
    public function closeTag()
    {
        return '</label>';
    }
}
