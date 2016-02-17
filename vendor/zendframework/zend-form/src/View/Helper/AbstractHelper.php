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
use Zend\I18n\View\Helper\AbstractTranslatorHelper as BaseAbstractHelper;
use Zend\View\Helper\Doctype;
use Zend\View\Helper\EscapeHtml;
use Zend\View\Helper\EscapeHtmlAttr;

/**
 * Base functionality for all form view helpers
 */
abstract class AbstractHelper extends BaseAbstractHelper
{
    /**
     * Standard boolean attributes, with expected values for enabling/disabling
     *
     * @var array
     */
    protected $booleanAttributes = array(
        'autofocus'    => array('on' => 'autofocus', 'off' => ''),
        'checked'      => array('on' => 'checked',   'off' => ''),
        'disabled'     => array('on' => 'disabled',  'off' => ''),
        'multiple'     => array('on' => 'multiple',  'off' => ''),
        'readonly'     => array('on' => 'readonly',  'off' => ''),
        'required'     => array('on' => 'required',  'off' => ''),
        'selected'     => array('on' => 'selected',  'off' => ''),
    );

    /**
     * Translatable attributes
     *
     * @var array
     */
    protected $translatableAttributes = array(
        'placeholder' => true,
        'title' => true,
    );

    /**
     * @var Doctype
     */
    protected $doctypeHelper;

    /**
     * @var EscapeHtml
     */
    protected $escapeHtmlHelper;

    /**
     * @var EscapeHtmlAttr
     */
    protected $escapeHtmlAttrHelper;

    /**
     * Attributes globally valid for all tags
     *
     * @var array
     */
    protected $validGlobalAttributes = array(
        'accesskey'          => true,
        'class'              => true,
        'contenteditable'    => true,
        'contextmenu'        => true,
        'dir'                => true,
        'draggable'          => true,
        'dropzone'           => true,
        'hidden'             => true,
        'id'                 => true,
        'lang'               => true,
        'onabort'            => true,
        'onblur'             => true,
        'oncanplay'          => true,
        'oncanplaythrough'   => true,
        'onchange'           => true,
        'onclick'            => true,
        'oncontextmenu'      => true,
        'ondblclick'         => true,
        'ondrag'             => true,
        'ondragend'          => true,
        'ondragenter'        => true,
        'ondragleave'        => true,
        'ondragover'         => true,
        'ondragstart'        => true,
        'ondrop'             => true,
        'ondurationchange'   => true,
        'onemptied'          => true,
        'onended'            => true,
        'onerror'            => true,
        'onfocus'            => true,
        'oninput'            => true,
        'oninvalid'          => true,
        'onkeydown'          => true,
        'onkeypress'         => true,
        'onkeyup'            => true,
        'onload'             => true,
        'onloadeddata'       => true,
        'onloadedmetadata'   => true,
        'onloadstart'        => true,
        'onmousedown'        => true,
        'onmousemove'        => true,
        'onmouseout'         => true,
        'onmouseover'        => true,
        'onmouseup'          => true,
        'onmousewheel'       => true,
        'onpause'            => true,
        'onplay'             => true,
        'onplaying'          => true,
        'onprogress'         => true,
        'onratechange'       => true,
        'onreadystatechange' => true,
        'onreset'            => true,
        'onscroll'           => true,
        'onseeked'           => true,
        'onseeking'          => true,
        'onselect'           => true,
        'onshow'             => true,
        'onstalled'          => true,
        'onsubmit'           => true,
        'onsuspend'          => true,
        'ontimeupdate'       => true,
        'onvolumechange'     => true,
        'onwaiting'          => true,
        'role'               => true,
        'aria-labelledby'    => true,
        'aria-describedby'   => true,
        'spellcheck'         => true,
        'style'              => true,
        'tabindex'           => true,
        'title'              => true,
        'xml:base'           => true,
        'xml:lang'           => true,
        'xml:space'          => true,
    );

    /**
     * Attributes valid for the tag represented by this helper
     *
     * This should be overridden in extending classes
     *
     * @var array
     */
    protected $validTagAttributes = array(
    );

    /**
     * Set value for doctype
     *
     * @param  string $doctype
     * @return AbstractHelper
     */
    public function setDoctype($doctype)
    {
        $this->getDoctypeHelper()->setDoctype($doctype);
        return $this;
    }

    /**
     * Get value for doctype
     *
     * @return string
     */
    public function getDoctype()
    {
        return $this->getDoctypeHelper()->getDoctype();
    }

    /**
     * Set value for character encoding
     *
     * @param  string $encoding
     * @return AbstractHelper
     */
    public function setEncoding($encoding)
    {
        $this->getEscapeHtmlHelper()->setEncoding($encoding);
        $this->getEscapeHtmlAttrHelper()->setEncoding($encoding);
        return $this;
    }

    /**
     * Get character encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->getEscapeHtmlHelper()->getEncoding();
    }

    /**
     * Create a string of all attribute/value pairs
     *
     * Escapes all attribute values
     *
     * @param  array $attributes
     * @return string
     */
    public function createAttributesString(array $attributes)
    {
        $attributes = $this->prepareAttributes($attributes);
        $escape     = $this->getEscapeHtmlHelper();
        $escapeAttr = $this->getEscapeHtmlAttrHelper();
        $strings    = array();

        foreach ($attributes as $key => $value) {
            $key = strtolower($key);

            if (!$value && isset($this->booleanAttributes[$key])) {
                // Skip boolean attributes that expect empty string as false value
                if ('' === $this->booleanAttributes[$key]['off']) {
                    continue;
                }
            }

            //check if attribute is translatable
            if (isset($this->translatableAttributes[$key]) && !empty($value)) {
                if (($translator = $this->getTranslator()) !== null) {
                    $value = $translator->translate($value, $this->getTranslatorTextDomain());
                }
            }

            //@TODO Escape event attributes like AbstractHtmlElement view helper does in htmlAttribs ??
            $strings[] = sprintf('%s="%s"', $escape($key), $escapeAttr($value));
        }

        return implode(' ', $strings);
    }

    /**
     * Get the ID of an element
     *
     * If no ID attribute present, attempts to use the name attribute.
     * If no name attribute is present, either, returns null.
     *
     * @param  ElementInterface $element
     * @return null|string
     */
    public function getId(ElementInterface $element)
    {
        $id = $element->getAttribute('id');
        if (null !== $id) {
            return $id;
        }

        return $element->getName();
    }

    /**
     * Get the closing bracket for an inline tag
     *
     * Closes as either "/>" for XHTML doctypes or ">" otherwise.
     *
     * @return string
     */
    public function getInlineClosingBracket()
    {
        $doctypeHelper = $this->getDoctypeHelper();
        if ($doctypeHelper->isXhtml()) {
            return '/>';
        }
        return '>';
    }

    /**
     * Retrieve the doctype helper
     *
     * @return Doctype
     */
    protected function getDoctypeHelper()
    {
        if ($this->doctypeHelper) {
            return $this->doctypeHelper;
        }

        if (method_exists($this->view, 'plugin')) {
            $this->doctypeHelper = $this->view->plugin('doctype');
        }

        if (!$this->doctypeHelper instanceof Doctype) {
            $this->doctypeHelper = new Doctype();
        }

        return $this->doctypeHelper;
    }

    /**
     * Retrieve the escapeHtml helper
     *
     * @return EscapeHtml
     */
    protected function getEscapeHtmlHelper()
    {
        if ($this->escapeHtmlHelper) {
            return $this->escapeHtmlHelper;
        }

        if (method_exists($this->view, 'plugin')) {
            $this->escapeHtmlHelper = $this->view->plugin('escapehtml');
        }

        if (!$this->escapeHtmlHelper instanceof EscapeHtml) {
            $this->escapeHtmlHelper = new EscapeHtml();
        }

        return $this->escapeHtmlHelper;
    }

    /**
     * Retrieve the escapeHtmlAttr helper
     *
     * @return EscapeHtmlAttr
     */
    protected function getEscapeHtmlAttrHelper()
    {
        if ($this->escapeHtmlAttrHelper) {
            return $this->escapeHtmlAttrHelper;
        }

        if (method_exists($this->view, 'plugin')) {
            $this->escapeHtmlAttrHelper = $this->view->plugin('escapehtmlattr');
        }

        if (!$this->escapeHtmlAttrHelper instanceof EscapeHtmlAttr) {
            $this->escapeHtmlAttrHelper = new EscapeHtmlAttr();
        }

        return $this->escapeHtmlAttrHelper;
    }

    /**
     * Prepare attributes for rendering
     *
     * Ensures appropriate attributes are present (e.g., if "name" is present,
     * but no "id", sets the latter to the former).
     *
     * Removes any invalid attributes
     *
     * @param  array $attributes
     * @return array
     */
    protected function prepareAttributes(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $attribute = strtolower($key);

            if (!isset($this->validGlobalAttributes[$attribute])
                && !isset($this->validTagAttributes[$attribute])
                && 'data-' != substr($attribute, 0, 5)
                && 'x-' != substr($attribute, 0, 2)
            ) {
                // Invalid attribute for the current tag
                unset($attributes[$key]);
                continue;
            }

            // Normalize attribute key, if needed
            if ($attribute != $key) {
                unset($attributes[$key]);
                $attributes[$attribute] = $value;
            }

            // Normalize boolean attribute values
            if (isset($this->booleanAttributes[$attribute])) {
                $attributes[$attribute] = $this->prepareBooleanAttributeValue($attribute, $value);
            }
        }

        return $attributes;
    }

    /**
     * Prepare a boolean attribute value
     *
     * Prepares the expected representation for the boolean attribute specified.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return string
     */
    protected function prepareBooleanAttributeValue($attribute, $value)
    {
        if (!is_bool($value) && in_array($value, $this->booleanAttributes[$attribute])) {
            return $value;
        }

        $value = (bool) $value;
        return ($value
            ? $this->booleanAttributes[$attribute]['on']
            : $this->booleanAttributes[$attribute]['off']
        );
    }
}
