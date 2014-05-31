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
 * @version    $Id: HtmlList.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * Zend_View_Helper_FormELement
 */
#require_once 'Zend/View/Helper/FormElement.php';

/**
 * Helper for ordered and unordered lists
 *
 * @uses Zend_View_Helper_FormElement
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_HtmlList extends Zend_View_Helper_FormElement
{

    /**
     * Generates a 'List' element.
     *
     * @param array   $items   Array with the elements of the list
     * @param boolean $ordered Specifies ordered/unordered list; default unordered
     * @param array   $attribs Attributes for the ol/ul tag.
     * @return string The list XHTML.
     */
    public function htmlList(array $items, $ordered = false, $attribs = false, $escape = true)
    {
        if (!is_array($items)) {
            #require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception('First param must be an array');
            $e->setView($this->view);
            throw $e;
        }

        $list = '';

        foreach ($items as $item) {
            if (!is_array($item)) {
                if ($escape) {
                    $item = $this->view->escape($item);
                }
                $list .= '<li>' . $item . '</li>' . self::EOL;
            } else {
                if (6 < strlen($list)) {
                    $list = substr($list, 0, strlen($list) - 6)
                     . $this->htmlList($item, $ordered, $attribs, $escape) . '</li>' . self::EOL;
                } else {
                    $list .= '<li>' . $this->htmlList($item, $ordered, $attribs, $escape) . '</li>' . self::EOL;
                }
            }
        }

        if ($attribs) {
            $attribs = $this->_htmlAttribs($attribs);
        } else {
            $attribs = '';
        }

        $tag = 'ul';
        if ($ordered) {
            $tag = 'ol';
        }

        return '<' . $tag . $attribs . '>' . self::EOL . $list . '</' . $tag . '>' . self::EOL;
    }
}
