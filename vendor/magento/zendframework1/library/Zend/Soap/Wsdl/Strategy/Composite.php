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
 * Zend_Soap_Wsdl_Strategy_Composite
 *
 * @category   Zend
 * @package    Zend_Soap
 * @subpackage Wsdl
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Soap_Wsdl_Strategy_Composite implements Zend_Soap_Wsdl_Strategy_Interface
{
    /**
     * Typemap of Complex Type => Strategy pairs.
     *
     * @var array
     */
    protected $_typeMap = array();

    /**
     * Default Strategy of this composite
     *
     * @var string|Zend_Soap_Wsdl_Strategy_Interface
     */
    protected $_defaultStrategy;

    /**
     * Context WSDL file that this composite serves
     *
     * @var Zend_Soap_Wsdl|null
     */
    protected $_context;

    /**
     * Construct Composite WSDL Strategy.
     *
     * @throws Zend_Soap_Wsdl_Exception
     * @param array $typeMap
     * @param string|Zend_Soap_Wsdl_Strategy_Interface $defaultStrategy
     */
    public function __construct(array $typeMap=array(), $defaultStrategy="Zend_Soap_Wsdl_Strategy_DefaultComplexType")
    {
        foreach($typeMap AS $type => $strategy) {
            $this->connectTypeToStrategy($type, $strategy);
        }
        $this->_defaultStrategy = $defaultStrategy;
    }

    /**
     * Connect a complex type to a given strategy.
     *
     * @throws Zend_Soap_Wsdl_Exception
     * @param  string $type
     * @param  string|Zend_Soap_Wsdl_Strategy_Interface $strategy
     * @return Zend_Soap_Wsdl_Strategy_Composite
     */
    public function connectTypeToStrategy($type, $strategy)
    {
        if(!is_string($type)) {
            /**
             * @see Zend_Soap_Wsdl_Exception
             */
            #require_once "Zend/Soap/Wsdl/Exception.php";
            throw new Zend_Soap_Wsdl_Exception("Invalid type given to Composite Type Map.");
        }
        $this->_typeMap[$type] = $strategy;
        return $this;
    }

    /**
     * Return default strategy of this composite
     *
     * @throws Zend_Soap_Wsdl_Exception
     * @param  string $type
     * @return Zend_Soap_Wsdl_Strategy_Interface
     */
    public function getDefaultStrategy()
    {
        $strategy = $this->_defaultStrategy;
        if(is_string($strategy) && class_exists($strategy)) {
            $strategy = new $strategy;
        }
        if( !($strategy instanceof Zend_Soap_Wsdl_Strategy_Interface) ) {
            /**
             * @see Zend_Soap_Wsdl_Exception
             */
            #require_once "Zend/Soap/Wsdl/Exception.php";
            throw new Zend_Soap_Wsdl_Exception(
                "Default Strategy for Complex Types is not a valid strategy object."
            );
        }
        $this->_defaultStrategy = $strategy;
        return $strategy;
    }

    /**
     * Return specific strategy or the default strategy of this type.
     *
     * @throws Zend_Soap_Wsdl_Exception
     * @param  string $type
     * @return Zend_Soap_Wsdl_Strategy_Interface
     */
    public function getStrategyOfType($type)
    {
        if(isset($this->_typeMap[$type])) {
            $strategy = $this->_typeMap[$type];

            if(is_string($strategy) && class_exists($strategy)) {
                $strategy = new $strategy();
            }

            if( !($strategy instanceof Zend_Soap_Wsdl_Strategy_Interface) ) {
                /**
                 * @see Zend_Soap_Wsdl_Exception
                 */
                #require_once "Zend/Soap/Wsdl/Exception.php";
                throw new Zend_Soap_Wsdl_Exception(
                    "Strategy for Complex Type '".$type."' is not a valid strategy object."
                );
            }
            $this->_typeMap[$type] = $strategy;
        } else {
            $strategy = $this->getDefaultStrategy();
        }
        return $strategy;
    }

    /**
     * Method accepts the current WSDL context file.
     *
     * @param Zend_Soap_Wsdl $context
     */
    public function setContext(Zend_Soap_Wsdl $context)
    {
        $this->_context = $context;
        return $this;
    }

    /**
     * Create a complex type based on a strategy
     *
     * @throws Zend_Soap_Wsdl_Exception
     * @param  string $type
     * @return string XSD type
     */
    public function addComplexType($type)
    {
        if(!($this->_context instanceof Zend_Soap_Wsdl) ) {
            /**
             * @see Zend_Soap_Wsdl_Exception
             */
            #require_once "Zend/Soap/Wsdl/Exception.php";
            throw new Zend_Soap_Wsdl_Exception(
                "Cannot add complex type '".$type."', no context is set for this composite strategy."
            );
        }

        $strategy = $this->getStrategyOfType($type);
        $strategy->setContext($this->_context);
        return $strategy->addComplexType($type);
    }
}
