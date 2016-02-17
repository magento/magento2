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
 * @subpackage AutoDiscover
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id:$
 */

class Zend_Soap_Server_Proxy
{
    /**
     * @var object
     */
    protected $_classInstance;
    /**
     * @var string
     */
    protected $_className;
    /**
     * Constructor
     * 
     * @param object $service 
     */
    public function  __construct($className, $classArgs = array())
    {
        $class = new ReflectionClass($className);
        $constructor = $class->getConstructor();
	if ($constructor === null) {
            $this->_classInstance = $class->newInstance();
	} else {
            $this->_classInstance = $class->newInstanceArgs($classArgs);
	}
	$this->_className = $className;
    }
    /**
     * Proxy for the WS-I compliant call
     * 
     * @param  string $name
     * @param  string $arguments
     * @return array 
     */
    public function __call($name, $arguments)
    {
        $result = call_user_func_array(array($this->_classInstance, $name), $this->_preProcessArguments($arguments));
        return array("{$name}Result"=>$result);
    }
    /**
     *  Pre process arguments
     * 
     * @param  mixed $arguments
     * @return array 
     */
    protected function _preProcessArguments($arguments)
    {
        if (count($arguments) == 1 && is_object($arguments[0])) {
            return get_object_vars($arguments[0]);
	} else {
            return $arguments;
	}
    }
}
