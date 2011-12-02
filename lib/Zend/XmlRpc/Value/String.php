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
 * @package    Zend_XmlRpc
 * @subpackage Value
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: String.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * Zend_XmlRpc_Value_Scalar
 */
#require_once 'Zend/XmlRpc/Value/Scalar.php';

/**
 * @package    Zend_XmlRpc
 * @subpackage Value
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_XmlRpc_Value_String extends Zend_XmlRpc_Value_Scalar
{

    /**
     * Set the value of a string native type
     *
     * @param string $value
     */
    public function __construct($value)
    {
        $this->_type = self::XMLRPC_TYPE_STRING;

        // Make sure this value is string and all XML characters are encoded
        $this->_value = (string)$value;
    }

    /**
     * Return the value of this object, convert the XML-RPC native string value into a PHP string
     *
     * @return string
     */
    public function getValue()
    {
        return (string)$this->_value;
    }
}