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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Soap_Wsdl_Strategy_Interface
 */
#require_once "Zend/Soap/Wsdl/Strategy/Interface.php";

/**
 * Abstract class for Zend_Soap_Wsdl_Strategy.
 *
 * @category   Zend
 * @package    Zend_Soap
 * @subpackage Wsdl
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Soap_Wsdl_Strategy_Abstract implements Zend_Soap_Wsdl_Strategy_Interface
{
    /**
     * Context object
     *
     * @var Zend_Soap_Wsdl
     */
    protected $_context;

    /**
     * Set the Zend_Soap_Wsdl Context object this strategy resides in.
     *
     * @param Zend_Soap_Wsdl $context
     * @return void
     */
    public function setContext(Zend_Soap_Wsdl $context)
    {
        $this->_context = $context;
    }

    /**
     * Return the current Zend_Soap_Wsdl context object
     *
     * @return Zend_Soap_Wsdl
     */
    public function getContext()
    {
        return $this->_context;
    }
}
