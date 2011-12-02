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
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: FormElement.php 22290 2010-05-25 14:27:12Z matthew $
 */

/**
 * @see Zend_View_Helper_HtmlElement
 */
#require_once 'Zend/View/Helper/HtmlElement.php';

/**
 * Base helper for form elements.  Extend this, don't use it on its own.
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_View_Helper_FormElement extends Zend_View_Helper_HtmlElement
{
    /**
     * @var Zend_Translate
     */
    protected $_translator;

    /**
     * Get translator
     *
     * @return Zend_Translate
     */
    public function getTranslator()
    {
         return $this->_translator;
    }

    /**
     * Set translator
     *
     * @param  $translator|null Zend_Translate
     * @return Zend_View_Helper_FormElement
     */
    public function setTranslator($translator = null)
    {
        if (null === $translator) {
            $this->_translator = null;
        } elseif ($translator instanceof Zend_Translate_Adapter) {
            $this->_translator = $translator;
        } elseif ($translator instanceof Zend_Translate) {
            $this->_translator = $translator->getAdapter();
        } else {
            #require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception('Invalid translator specified');
            $e->setView($this->view);
            throw $e;
        }
         return $this;
    }

    /**
     * Converts parameter arguments to an element info array.
     *
     * E.g, formExample($name, $value, $attribs, $options, $listsep) is
     * the same thing as formExample(array('name' => ...)).
     *
     * Note that you cannot pass a 'disable' param; you need to pass
     * it as an 'attribs' key.
     *
     * @access protected
     *
     * @return array An element info array with keys for name, value,
     * attribs, options, listsep, disable, and escape.
     */
    protected function _getInfo($name, $value = null, $attribs = null,
        $options = null, $listsep = null
    ) {
        // the baseline info.  note that $name serves a dual purpose;
        // if an array, it's an element info array that will override
        // these baseline values.  as such, ignore it for the 'name'
        // if it's an array.
        $info = array(
            'name'    => is_array($name) ? '' : $name,
            'id'      => is_array($name) ? '' : $name,
            'value'   => $value,
            'attribs' => $attribs,
            'options' => $options,
            'listsep' => $listsep,
            'disable' => false,
            'escape'  => true,
        );

        // override with named args
        if (is_array($name)) {
            // only set keys that are already in info
            foreach ($info as $key => $val) {
                if (isset($name[$key])) {
                    $info[$key] = $name[$key];
                }
            }

            // If all helper options are passed as an array, attribs may have 
            // been as well
            if (null === $attribs) {
                $attribs = $info['attribs'];
            }
        }

        $attribs = (array)$attribs;

        // Normalize readonly tag
        if (array_key_exists('readonly', $attribs)) {
            $attribs['readonly'] = 'readonly';
        }

        // Disable attribute
        if (array_key_exists('disable', $attribs)) {
           if (is_scalar($attribs['disable'])) {
                // disable the element
                $info['disable'] = (bool)$attribs['disable'];
            } else if (is_array($attribs['disable'])) {
                $info['disable'] = $attribs['disable'];
            }
        }

        // Set ID for element
        if (array_key_exists('id', $attribs)) {
            $info['id'] = (string)$attribs['id'];
        } else if ('' !== $info['name']) {
            $info['id'] = trim(strtr($info['name'],
                                     array('[' => '-', ']' => '')), '-');
        }

        // Determine escaping from attributes
        if (array_key_exists('escape', $attribs)) {
            $info['escape'] = (bool)$attribs['escape'];
        }

        // Determine listsetp from attributes
        if (array_key_exists('listsep', $attribs)) {
            $info['listsep'] = (string)$attribs['listsep'];
        }

        // Remove attribs that might overwrite the other keys. We do this LAST
        // because we needed the other attribs values earlier.
        foreach ($info as $key => $val) {
            if (array_key_exists($key, $attribs)) {
                unset($attribs[$key]);
            }
        }
        $info['attribs'] = $attribs;

        // done!
        return $info;
    }

    /**
     * Creates a hidden element.
     *
     * We have this as a common method because other elements often
     * need hidden elements for their operation.
     *
     * @access protected
     *
     * @param $name The element name.
     *
     * @param $value The element value.
     *
     * @param $attribs Attributes for the element.
     *
     * @return string A hidden element.
     */
    protected function _hidden($name, $value = null, $attribs = null)
    {
        return '<input type="hidden"'
             . ' name="' . $this->view->escape($name) . '"'
             . ' value="' . $this->view->escape($value) . '"'
             . $this->_htmlAttribs($attribs) . $this->getClosingBracket();
    }
}
