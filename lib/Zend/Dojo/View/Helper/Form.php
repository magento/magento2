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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Form.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/** Zend_Dojo_View_Helper_Dijit */
#require_once 'Zend/Dojo/View/Helper/Dijit.php';

/**
 * Dojo Form dijit
 *
 * @uses       Zend_Dojo_View_Helper_Dijit
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
  */
class Zend_Dojo_View_Helper_Form extends Zend_Dojo_View_Helper_Dijit
{
    /**
     * Dijit being used
     * @var string
     */
    protected $_dijit  = 'dijit.form.Form';

    /**
     * Module being used
     * @var string
     */
    protected $_module = 'dijit.form.Form';

    /**
     * @var Zend_View_Helper_Form
     */
    protected $_helper;

    /**
     * dijit.form.Form
     *
     * @param  string $id
     * @param  null|array $attribs HTML attributes
     * @param  false|string $content
     * @return string
     */
    public function form($id, $attribs = null, $content = false)
    {
        if (!is_array($attribs)) {
            $attribs = (array) $attribs;
        }
        if (array_key_exists('id', $attribs)) {
            $attribs['name'] = $id;
        } else {
            $attribs['id'] = $id;
        }

        if (false === $content) {
            $content = '';
        }

        $attribs = $this->_prepareDijit($attribs, array(), 'layout');

        return $this->getFormHelper()->form($id, $attribs, $content);
    }

    /**
     * Get standard form helper
     *
     * @return Zend_View_Helper_Form
     */
    public function getFormHelper()
    {
        if (null === $this->_helper) {
            #require_once 'Zend/View/Helper/Form.php';
            $this->_helper = new Zend_View_Helper_Form;
            $this->_helper->setView($this->view);
        }
        return $this->_helper;
    }
}
