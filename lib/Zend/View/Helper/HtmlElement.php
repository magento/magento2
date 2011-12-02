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
 * @version    $Id: HtmlElement.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_View_Helper_Abstract
 */
#require_once 'Zend/View/Helper/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_View_Helper_HtmlElement extends Zend_View_Helper_Abstract
{
    /**
     * EOL character
     */
    const EOL = "\n";

    /**
     * The tag closing bracket
     *
     * @var string
     */
    protected $_closingBracket = null;

    /**
     * Get the tag closing bracket
     *
     * @return string
     */
    public function getClosingBracket()
    {
        if (!$this->_closingBracket) {
            if ($this->_isXhtml()) {
                $this->_closingBracket = ' />';
            } else {
                $this->_closingBracket = '>';
            }
        }

        return $this->_closingBracket;
    }

    /**
     * Is doctype XHTML?
     *
     * @return boolean
     */
    protected function _isXhtml()
    {
        $doctype = $this->view->doctype();
        return $doctype->isXhtml();
    }

    /**
     * Converts an associative array to a string of tag attributes.
     *
     * @access public
     *
     * @param array $attribs From this array, each key-value pair is
     * converted to an attribute name and value.
     *
     * @return string The XHTML for the attributes.
     */
    protected function _htmlAttribs($attribs)
    {
        $xhtml = '';
        foreach ((array) $attribs as $key => $val) {
            $key = $this->view->escape($key);

            if (('on' == substr($key, 0, 2)) || ('constraints' == $key)) {
                // Don't escape event attributes; _do_ substitute double quotes with singles
                if (!is_scalar($val)) {
                    // non-scalar data should be cast to JSON first
                    #require_once 'Zend/Json.php';
                    $val = Zend_Json::encode($val);
                }
                $val = preg_replace('/"([^"]*)":/', '$1:', $val);
            } else {
                if (is_array($val)) {
                    $val = implode(' ', $val);
                }
                $val = $this->view->escape($val);
            }

            if ('id' == $key) {
                $val = $this->_normalizeId($val);
            }

            if (strpos($val, '"') !== false) {
                $xhtml .= " $key='$val'";
            } else {
                $xhtml .= " $key=\"$val\"";
            }

        }
        return $xhtml;
    }

    /**
     * Normalize an ID
     *
     * @param  string $value
     * @return string
     */
    protected function _normalizeId($value)
    {
        if (strstr($value, '[')) {
            if ('[]' == substr($value, -2)) {
                $value = substr($value, 0, strlen($value) - 2);
            }
            $value = trim($value, ']');
            $value = str_replace('][', '-', $value);
            $value = str_replace('[', '-', $value);
        }
        return $value;
    }
}
