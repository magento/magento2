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
 * @version    $Id: Form.php 20096 2010-01-06 02:05:09Z bkarwin $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_View_Helper_FormElement */
#require_once 'Zend/View/Helper/FormElement.php';

/**
 * Helper for rendering HTML forms
 *
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_Form extends Zend_View_Helper_FormElement
{
    /**
     * Render HTML form
     *
     * @param  string $name Form name
     * @param  null|array $attribs HTML form attributes
     * @param  false|string $content Form content
     * @return string
     */
    public function form($name, $attribs = null, $content = false)
    {
        $info = $this->_getInfo($name, $content, $attribs);
        extract($info);

        if (!empty($id)) {
            $id = ' id="' . $this->view->escape($id) . '"';
        } else {
            $id = '';
        }

        if (array_key_exists('id', $attribs) && empty($attribs['id'])) {
            unset($attribs['id']);
        }

        $xhtml = '<form'
               . $id
               . $this->_htmlAttribs($attribs)
               . '>';

        if (false !== $content) {
            $xhtml .= $content
                   .  '</form>';
        }

        return $xhtml;
    }
}
