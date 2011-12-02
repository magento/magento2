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
 * @package    Zend_Soap
 * @subpackage Wsdl
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: AnyType.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Soap_Wsdl_Strategy_Interface
 */
#require_once "Zend/Soap/Wsdl/Strategy/Interface.php";

/**
 * Zend_Soap_Wsdl_Strategy_AnyType
 *
 * @category   Zend
 * @package    Zend_Soap
 * @subpackage Wsdl
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Soap_Wsdl_Strategy_AnyType implements Zend_Soap_Wsdl_Strategy_Interface
{
    /**
     * Not needed in this strategy.
     *
     * @param Zend_Soap_Wsdl $context
     */
    public function setContext(Zend_Soap_Wsdl $context)
    {

    }

    /**
     * Returns xsd:anyType regardless of the input.
     *
     * @param string $type
     * @return string
     */
    public function addComplexType($type)
    {
        return 'xsd:anyType';
    }
}