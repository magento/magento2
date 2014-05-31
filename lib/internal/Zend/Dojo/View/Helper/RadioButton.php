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
 * @version    $Id: RadioButton.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/** Zend_Dojo_View_Helper_Dijit */
#require_once 'Zend/Dojo/View/Helper/Dijit.php';

/**
 * Dojo RadioButton dijit
 *
 * @uses       Zend_Dojo_View_Helper_Dijit
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
  */
class Zend_Dojo_View_Helper_RadioButton extends Zend_Dojo_View_Helper_Dijit
{
    /**
     * Dijit being used
     * @var string
     */
    protected $_dijit  = 'dijit.form.RadioButton';

    /**
     * Dojo module to use
     * @var string
     */
    protected $_module = 'dijit.form.CheckBox';

    /**
     * dijit.form.RadioButton
     *
     * @param  string $id
     * @param  string $value
     * @param  array $params  Parameters to use for dijit creation
     * @param  array $attribs HTML attributes
     * @param  array $options Array of radio options
     * @param  string $listsep String with which to separate options
     * @return string
     */
    public function radioButton(
        $id,
        $value = null,
        array $params = array(),
        array $attribs = array(),
        array $options = null,
        $listsep = "<br />\n"
    ) {
        $attribs['name'] = $id;
        if (!array_key_exists('id', $attribs)) {
            $attribs['id'] = $id;
        }
        $attribs = $this->_prepareDijit($attribs, $params, 'element');

        if (is_array($options) && $this->_useProgrammatic() && !$this->_useProgrammaticNoScript()) {
            $baseId = $id;
            if (array_key_exists('id', $attribs)) {
                $baseId = $attribs['id'];
            }
            #require_once 'Zend/Filter/Alnum.php';
            $filter = new Zend_Filter_Alnum();
            foreach (array_keys($options) as $key) {
                $optId = $baseId . '-' . $filter->filter($key);
                $this->_createDijit($this->_dijit, $optId, array());
            }
        }

        return $this->view->formRadio($id, $value, $attribs, $options, $listsep);
    }
}
