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
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Dojo_View_Helper_Dijit */
#require_once 'Zend/Dojo/View/Helper/Dijit.php';

/**
 * dijit.form.SimpleTextarea view helper
 *
 * @uses       Zend_Dojo_View_Helper_Dijit
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Dojo_View_Helper_SimpleTextarea extends Zend_Dojo_View_Helper_Dijit
{
    /**
     * @var string Dijit type
     */
    protected $_dijit  = 'dijit.form.SimpleTextarea';

    /**
     * @var string HTML element type
     */
    protected $_elementType = 'textarea';

    /**
     * @var string Dojo module
     */
    protected $_module = 'dijit.form.SimpleTextarea';

    /**
     * dijit.form.SimpleTextarea
     *
     * @param  string $id
     * @param  string $value
     * @param  array $params  Parameters to use for dijit creation
     * @param  array $attribs HTML attributes
     * @return string
     */
    public function simpleTextarea($id, $value = null, array $params = array(), array $attribs = array())
    {
        if (!array_key_exists('id', $attribs)) {
            $attribs['id']    = $id;
        }
        $attribs['name']  = $id;

        $attribs = $this->_prepareDijit($attribs, $params, 'textarea');

        $html = '<textarea' . $this->_htmlAttribs($attribs) . '>'
              . $this->view->escape($value)
              . "</textarea>\n";

        return $html;
    }
}
