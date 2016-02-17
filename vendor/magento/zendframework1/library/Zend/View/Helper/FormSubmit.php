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
 * Helper to generate a "submit" button
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_FormSubmit extends Zend_View_Helper_FormElement
{
    /**
     * Generates a 'submit' button.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are extracted in place of added parameters.
     *
     * @param mixed $value The element value.
     *
     * @param array $attribs Attributes for the element tag.
     *
     * @return string The element XHTML.
     */
    public function formSubmit($name, $value = null, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable, id
        // check if disabled
        $disabled = '';
        if ($disable) {
            $disabled = ' disabled="disabled"';
        }

        if ($id) {
            $id = ' id="' . $this->view->escape($id) . '"';
        }

        // Render the button.
        $xhtml = '<input type="submit"'
               . ' name="' . $this->view->escape($name) . '"'
               . $id
               . ' value="' . $this->view->escape($value) . '"'
               . $disabled
               . $this->_htmlAttribs($attribs)
               . $this->getClosingBracket();

        return $xhtml;
    }
}
