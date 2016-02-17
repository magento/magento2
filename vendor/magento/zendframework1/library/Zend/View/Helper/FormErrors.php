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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Abstract class for extension
 */
#require_once 'Zend/View/Helper/FormElement.php';


/**
 * Helper to render errors for a form element
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_FormErrors extends Zend_View_Helper_FormElement
{
    /**
     * @var Zend_Form_Element
     */
    protected $_element;

    /**#@+
     * @var string Element block start/end tags and separator
     */
    protected $_htmlElementEnd       = '</li></ul>';
    protected $_htmlElementStart     = '<ul%s><li>';
    protected $_htmlElementSeparator = '</li><li>';
    /**#@-*/

    /**
     * Render form errors
     *
     * @param  string|array $errors Error(s) to render
     * @param  array $options
     * @return string
     */
    public function formErrors($errors, array $options = null)
    {
        $escape = true;
        if (isset($options['escape'])) {
            $escape = (bool) $options['escape'];
            unset($options['escape']);
        }

        if (empty($options['class'])) {
            $options['class'] = 'errors';
        }

        if (isset($options['elementStart'])) {
            $this->setElementStart($options['elementStart']);
        }
        if (isset($options['elementEnd'])) {
            $this->setElementEnd($options['elementEnd']);
        }
        if (isset($options['elementSeparator'])) {
            $this->setElementSeparator($options['elementSeparator']);
        }

        $start = $this->getElementStart();
        if (strstr($start, '%s')) {
            $attribs = $this->_htmlAttribs($options);
            $start   = sprintf($start, $attribs);
        }

        if ($escape) {
            foreach ($errors as $key => $error) {
                $errors[$key] = $this->view->escape($error);
            }
        }

        $html  = $start
               . implode($this->getElementSeparator(), (array) $errors)
               . $this->getElementEnd();

        return $html;
    }

    /**
     * Set end string for displaying errors
     *
     * @param  string $string
     * @return Zend_View_Helper_FormErrors
     */
    public function setElementEnd($string)
    {
        $this->_htmlElementEnd = (string) $string;
        return $this;
    }

    /**
     * Retrieve end string for displaying errors
     *
     * @return string
     */
    public function getElementEnd()
    {
        return $this->_htmlElementEnd;
    }

    /**
     * Set separator string for displaying errors
     *
     * @param  string $string
     * @return Zend_View_Helper_FormErrors
     */
    public function setElementSeparator($string)
    {
        $this->_htmlElementSeparator = (string) $string;
        return $this;
    }

    /**
     * Retrieve separator string for displaying errors
     *
     * @return string
     */
    public function getElementSeparator()
    {
        return $this->_htmlElementSeparator;
    }

    /**
     * Set start string for displaying errors
     *
     * @param  string $string
     * @return Zend_View_Helper_FormErrors
     */
    public function setElementStart($string)
    {
        $this->_htmlElementStart = (string) $string;
        return $this;
    }

    /**
     * Retrieve start string for displaying errors
     *
     * @return string
     */
    public function getElementStart()
    {
        return $this->_htmlElementStart;
    }

}
