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
 * @version    $Id: NumberSpinner.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/** Zend_Dojo_View_Helper_Dijit */
#require_once 'Zend/Dojo/View/Helper/Dijit.php';

/**
 * Dojo NumberSpinner dijit
 *
 * @uses       Zend_Dojo_View_Helper_Dijit
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
  */
class Zend_Dojo_View_Helper_NumberSpinner extends Zend_Dojo_View_Helper_Dijit
{
    /**
     * Dijit being used
     * @var string
     */
    protected $_dijit  = 'dijit.form.NumberSpinner';

    /**
     * HTML element type
     * @var string
     */
    protected $_elementType = 'text';

    /**
     * Dojo module to use
     * @var string
     */
    protected $_module = 'dijit.form.NumberSpinner';

    /**
     * dijit.form.NumberSpinner
     *
     * @param  int $id
     * @param  mixed $value
     * @param  array $params  Parameters to use for dijit creation
     * @param  array $attribs HTML attributes
     * @return string
     */
    public function numberSpinner($id, $value = null, array $params = array(), array $attribs = array())
    {
        // Get constraints and serialize to JSON if necessary
        if (array_key_exists('constraints', $params)) {
            if (!is_array($params['constraints'])) {
                unset($params['constraints']);
            }
        } else {
            $constraints = array();
            if (array_key_exists('min', $params)) {
                $constraints['min'] = $params['min'];
                unset($params['min']);
            }
            if (array_key_exists('max', $params)) {
                $constraints['max'] = $params['max'];
                unset($params['max']);
            }
            if (array_key_exists('places', $params)) {
                $constraints['places'] = $params['places'];
                unset($params['places']);
            }
            $params['constraints'] = $constraints;
        }

        return $this->_createFormElement($id, $value, $params, $attribs);
    }
}
