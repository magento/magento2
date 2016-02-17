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
 * @package    Zend_CodeGenerator
 * @subpackage Php
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * A value-holder object for non-expressable parameter default values, such as null, booleans and empty array()
 *
 * @category   Zend
 * @package    Zend_CodeGenerator
 * @subpackage Php
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_CodeGenerator_Php_Parameter_DefaultValue
{
    /**
     * @var string
     */
    protected $_defaultValue = null;

    /**
     *
     * @param string $defaultValue
     * @throws Zend_CodeGenerator_Php_Exception
     */
    public function __construct($defaultValue)
    {
        if(!is_string($defaultValue)) {
            #require_once "Zend/CodeGenerator/Php/Exception.php";
            throw new Zend_CodeGenerator_Php_Exception(
                "Can only set a string as default value representation, ".
                "but ".gettype($defaultValue)." was given."
            );
        }
        $this->_defaultValue = $defaultValue;
    }

    public function __toString()
    {
        return $this->_defaultValue;
    }
}
