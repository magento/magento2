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
 * @package    Zend_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Form_Element_Xhtml */
#require_once 'Zend/Form/Element/Xhtml.php';

/**
 * Element to show an HTML note
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Form_Element_Note extends Zend_Form_Element_Xhtml
{
    /**
     * Default form view helper to use for rendering
     *
     * @var string
     */
    public $helper = 'formNote';

    /**
     * Ignore flag (used when retrieving values at form level)
     *
     * @var bool
     */
    protected $_ignore = true;

    /**
     * Validate element value (pseudo)
     *
     * There is no need to reset the value
     *
     * @param  mixed $value Is always ignored
     * @param  mixed $context Is always ignored
     * @return boolean Returns always TRUE
     */
    public function isValid($value, $context = null)
    {
        return true;
    }
}
