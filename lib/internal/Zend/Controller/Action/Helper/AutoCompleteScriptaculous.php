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
 * @package    Zend_Controller
 * @subpackage Zend_Controller_Action_Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: AutoCompleteScriptaculous.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Controller_Action_Helper_AutoComplete_Abstract
 */
#require_once 'Zend/Controller/Action/Helper/AutoComplete/Abstract.php';

/**
 * Create and send Scriptaculous-compatible autocompletion lists
 *
 * @uses       Zend_Controller_Action_Helper_AutoComplete_Abstract
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Zend_Controller_Action_Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Controller_Action_Helper_AutoCompleteScriptaculous extends Zend_Controller_Action_Helper_AutoComplete_Abstract
{
    /**
     * Validate data for autocompletion
     *
     * @param  mixed $data
     * @return bool
     */
    public function validateData($data)
    {
        if (!is_array($data) && !is_scalar($data)) {
            return false;
        }

        return true;
    }

    /**
     * Prepare data for autocompletion
     *
     * @param  mixed   $data
     * @param  boolean $keepLayouts
     * @throws Zend_Controller_Action_Exception
     * @return string
     */
    public function prepareAutoCompletion($data, $keepLayouts = false)
    {
        if (!$this->validateData($data)) {
            /**
             * @see Zend_Controller_Action_Exception
             */
            #require_once 'Zend/Controller/Action/Exception.php';
            throw new Zend_Controller_Action_Exception('Invalid data passed for autocompletion');
        }

        $data = (array) $data;
        $data = '<ul><li>' . implode('</li><li>', $data) . '</li></ul>';

        if (!$keepLayouts) {
            $this->disableLayouts();
        }

        return $data;
    }
}
