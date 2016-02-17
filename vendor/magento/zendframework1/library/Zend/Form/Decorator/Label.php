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
 * @package    Zend_Form
 * @subpackage Decorator
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Form_Decorator_Abstract */
#require_once 'Zend/Form/Decorator/Abstract.php';

/**
 * Zend_Form_Decorator_Label
 *
 * Accepts the options:
 * - separator: separator to use between label and content (defaults to PHP_EOL)
 * - placement: whether to append or prepend label to content (defaults to prepend)
 * - tag: if set, used to wrap the label in an additional HTML tag
 * - tagClass: if tag option is set, used to add a class to the label wrapper
 * - opt(ional)Prefix: a prefix to the label to use when the element is optional
 * - opt(ional)Suffix: a suffix to the label to use when the element is optional
 * - req(uired)Prefix: a prefix to the label to use when the element is required
 * - req(uired)Suffix: a suffix to the label to use when the element is required
 *
 * Any other options passed will be used as HTML attributes of the label tag.
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Decorator
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Form_Decorator_Label extends Zend_Form_Decorator_Abstract
{
    /**
     * Placement constants
     */
    const IMPLICIT         = 'IMPLICIT';
    const IMPLICIT_PREPEND = 'IMPLICIT_PREPEND';
    const IMPLICIT_APPEND  = 'IMPLICIT_APPEND';

    /**
     * Default placement: prepend
     * @var string
     */
    protected $_placement = 'PREPEND';

    /**
     * HTML tag with which to surround label
     * @var string
     */
    protected $_tag;

    /**
     * Class for the HTML tag with which to surround label
     * @var string
     */
    protected $_tagClass;

    /**
     * Set element ID
     *
     * @param  string $id
     * @return Zend_Form_Decorator_Label
     */
    public function setId($id)
    {
        $this->setOption('id', $id);
        return $this;
    }

    /**
     * Retrieve element ID (used in 'for' attribute)
     *
     * If none set in decorator, looks first for element 'id' attribute, and
     * defaults to element name.
     *
     * @return string
     */
    public function getId()
    {
        $id = $this->getOption('id');
        if (null === $id) {
            if (null !== ($element = $this->getElement())) {
                $id = $element->getId();
                $this->setId($id);
            }
        }

        return $id;
    }

    /**
     * Set HTML tag with which to surround label
     *
     * @param  string $tag
     * @return Zend_Form_Decorator_Label
     */
    public function setTag($tag)
    {
        if (empty($tag)) {
            $this->_tag = null;
        } else {
            $this->_tag = (string) $tag;
        }

        $this->removeOption('tag');

        return $this;
    }

    /**
     * Get HTML tag, if any, with which to surround label
     *
     * @return string
     */
    public function getTag()
    {
        if (null === $this->_tag) {
            $tag = $this->getOption('tag');
            if (null !== $tag) {
                $this->removeOption('tag');
                $this->setTag($tag);
            }
            return $tag;
        }

        return $this->_tag;
    }

    /**
     * Set the class to apply to the HTML tag with which to surround label
     *
     * @param  string $tagClass
     * @return Zend_Form_Decorator_Label
     */
    public function setTagClass($tagClass)
    {
        if (empty($tagClass)) {
            $this->_tagClass = null;
        } else {
            $this->_tagClass = (string) $tagClass;
        }

        $this->removeOption('tagClass');

        return $this;
    }

    /**
     * Get the class to apply to the HTML tag, if any, with which to surround label
     *
     * @return void
     */
    public function getTagClass()
    {
        if (null === $this->_tagClass) {
            $tagClass = $this->getOption('tagClass');
            if (null !== $tagClass) {
                $this->removeOption('tagClass');
                $this->setTagClass($tagClass);
            }
        }

        return $this->_tagClass;
    }

    /**
     * Get class with which to define label
     *
     * Appends either 'optional' or 'required' to class, depending on whether
     * or not the element is required.
     *
     * @return string
     */
    public function getClass()
    {
        $class   = '';
        $element = $this->getElement();

        $decoratorClass = $this->getOption('class');
        if (!empty($decoratorClass)) {
            $class .= ' ' . $decoratorClass;
        }

        $type  = $element->isRequired() ? 'required' : 'optional';

        if (!strstr($class, $type)) {
            $class .= ' ' . $type;
            $class = trim($class);
        }

        return $class;
    }

    /**
     * Load an optional/required suffix/prefix key
     *
     * @param  string $key
     * @return void
     */
    protected function _loadOptReqKey($key)
    {
        if (!isset($this->$key)) {
            $value = $this->getOption($key);
            $this->$key = (string) $value;
            if (null !== $value) {
                $this->removeOption($key);
            }
        }
    }

    /**
     * Overloading
     *
     * Currently overloads:
     *
     * - getOpt(ional)Prefix()
     * - getOpt(ional)Suffix()
     * - getReq(uired)Prefix()
     * - getReq(uired)Suffix()
     * - setOpt(ional)Prefix()
     * - setOpt(ional)Suffix()
     * - setReq(uired)Prefix()
     * - setReq(uired)Suffix()
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     * @throws Zend_Form_Exception for unsupported methods
     */
    public function __call($method, $args)
    {
        $tail = substr($method, -6);
        $head = substr($method, 0, 3);
        if (in_array($head, array('get', 'set'))
            && (('Prefix' == $tail) || ('Suffix' == $tail))
        ) {
            $position = substr($method, -6);
            $type     = strtolower(substr($method, 3, 3));
            switch ($type) {
                case 'req':
                    $key = 'required' . $position;
                    break;
                case 'opt':
                    $key = 'optional' . $position;
                    break;
                default:
                    #require_once 'Zend/Form/Exception.php';
                    throw new Zend_Form_Exception(sprintf('Invalid method "%s" called in Label decorator, and detected as type %s', $method, $type));
            }

            switch ($head) {
                case 'set':
                    if (0 === count($args)) {
                        #require_once 'Zend/Form/Exception.php';
                        throw new Zend_Form_Exception(sprintf('Method "%s" requires at least one argument; none provided', $method));
                    }
                    $value = array_shift($args);
                    $this->$key = $value;
                    return $this;
                case 'get':
                default:
                    if (null === ($element = $this->getElement())) {
                        $this->_loadOptReqKey($key);
                    } elseif (isset($element->$key)) {
                        $this->$key = (string) $element->$key;
                    } else {
                        $this->_loadOptReqKey($key);
                    }
                    return $this->$key;
            }
        }

        #require_once 'Zend/Form/Exception.php';
        throw new Zend_Form_Exception(sprintf('Invalid method "%s" called in Label decorator', $method));
    }

    /**
     * Get label to render
     *
     * @return string
     */
    public function getLabel()
    {
        if (null === ($element = $this->getElement())) {
            return '';
        }

        $label = $element->getLabel();
        $label = trim($label);

        if (empty($label)) {
            return '';
        }

        $optPrefix = $this->getOptPrefix();
        $optSuffix = $this->getOptSuffix();
        $reqPrefix = $this->getReqPrefix();
        $reqSuffix = $this->getReqSuffix();
        $separator = $this->getSeparator();

        if (!empty($label)) {
            if ($element->isRequired()) {
                $label = $reqPrefix . $label . $reqSuffix;
            } else {
                $label = $optPrefix . $label . $optSuffix;
            }
        }

        return $label;
    }

    /**
     * Determine if label should append, prepend or implicit content
     *
     * @return string
     */
    public function getPlacement()
    {
        $placement = $this->_placement;
        if (null !== ($placementOpt = $this->getOption('placement'))) {
            $placementOpt = strtoupper($placementOpt);
            switch ($placementOpt) {
                case self::APPEND:
                case self::PREPEND:
                case self::IMPLICIT:
                case self::IMPLICIT_PREPEND:
                case self::IMPLICIT_APPEND:
                    $placement = $this->_placement = $placementOpt;
                    break;
                case false:
                    $placement = $this->_placement = null;
                    break;
                default:
                    break;
            }
            $this->removeOption('placement');
        }

        return $placement;
    }

    /**
     * Render a label
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        $view    = $element->getView();
        if (null === $view) {
            return $content;
        }

        $label     = $this->getLabel();
        $separator = $this->getSeparator();
        $placement = $this->getPlacement();
        $tag       = $this->getTag();
        $tagClass  = $this->getTagClass();
        $id        = $this->getId();
        $class     = $this->getClass();
        $options   = $this->getOptions();


        if (empty($label) && empty($tag)) {
            return $content;
        }

        if (!empty($label)) {
            $options['class'] = $class;
            $label            = trim($label);

            switch ($placement) {
                case self::IMPLICIT:
                    // Break was intentionally omitted

                case self::IMPLICIT_PREPEND:
                    $options['escape']     = false;
                    $options['disableFor'] = true;

                    $label = $view->formLabel(
                        $element->getFullyQualifiedName(),
                        $label . $separator . $content,
                        $options
                    );
                    break;

                case self::IMPLICIT_APPEND:
                    $options['escape']     = false;
                    $options['disableFor'] = true;

                    $label = $view->formLabel(
                        $element->getFullyQualifiedName(),
                        $content . $separator . $label,
                        $options
                    );
                    break;

                case self::APPEND:
                    // Break was intentionally omitted

                case self::PREPEND:
                    // Break was intentionally omitted

                default:
                    $label = $view->formLabel(
                        $element->getFullyQualifiedName(),
                        $label,
                        $options
                    );
                    break;
            }
        } else {
            $label = '&#160;';
        }

        if (null !== $tag) {
            #require_once 'Zend/Form/Decorator/HtmlTag.php';
            $decorator = new Zend_Form_Decorator_HtmlTag();
            if (null !== $this->_tagClass) {
                $decorator->setOptions(array('tag'   => $tag,
                                             'id'    => $id . '-label',
                                             'class' => $tagClass));
            } else {
                $decorator->setOptions(array('tag'   => $tag,
                                             'id'    => $id . '-label'));
            }

            $label = $decorator->render($label);
        }

        switch ($placement) {
            case self::APPEND:
                return $content . $separator . $label;

            case self::PREPEND:
                return $label . $separator . $content;

            case self::IMPLICIT:
                // Break was intentionally omitted

            case self::IMPLICIT_PREPEND:
                // Break was intentionally omitted

            case self::IMPLICIT_APPEND:
                return $label;
        }
    }
}
