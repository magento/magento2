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
 * @version    $Id$
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_View_Helper_Abstract.php */
#require_once 'Zend/View/Helper/Abstract.php';

/**
 * Helper for declaring default values of template variables
 *
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_DeclareVars extends Zend_View_Helper_Abstract
{
    /**
     * The view object that created this helper object.
     * @var Zend_View
     */
    public $view;

    /**
     * Declare template vars to set default values and avoid notices when using strictVars
     *
     * Primarily for use when using {@link Zend_View_Abstract::strictVars() Zend_View strictVars()},
     * this helper can be used to declare template variables that may or may
     * not already be set in the view object, as well as to set default values.
     * Arrays passed as arguments to the method will be used to set default
     * values; otherwise, if the variable does not exist, it is set to an empty
     * string.
     *
     * Usage:
     * <code>
     * $this->declareVars(
     *     'varName1',
     *     'varName2',
     *     array('varName3' => 'defaultValue',
     *           'varName4' => array()
     *     )
     * );
     * </code>
     *
     * @param string|array variable number of arguments, all string names of variables to test
     * @return void
     */
    public function declareVars()
    {
        $args = func_get_args();
        foreach($args as $key) {
            if (is_array($key)) {
                foreach ($key as $name => $value) {
                    $this->_declareVar($name, $value);
                }
            } else if (!isset($view->$key)) {
                $this->_declareVar($key);
            }
        }
    }

    /**
     * Set a view variable
     *
     * Checks to see if a $key is set in the view object; if not, sets it to $value.
     *
     * @param  string $key
     * @param  string $value Defaults to an empty string
     * @return void
     */
    protected function _declareVar($key, $value = '')
    {
        if (!isset($this->view->$key)) {
            $this->view->$key = $value;
        }
    }
}
