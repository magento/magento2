<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Soap\AutoDiscover\DiscoveryStrategy;

use Zend\Server\Reflection\AbstractFunction;
use Zend\Server\Reflection\Prototype;
use Zend\Server\Reflection\ReflectionParameter;

/**
 * Describes how types, return values and method details are detected during
 * AutoDiscovery of a WSDL.
 */
class ReflectionDiscovery implements DiscoveryStrategyInterface
{
    /**
     * Returns description from phpdoc block
     *
     * @param  AbstractFunction $function
     * @return string
     */
    public function getFunctionDocumentation(AbstractFunction $function)
    {
        return $function->getDescription();
    }

    /**
     * Return parameter type
     *
     * @param  ReflectionParameter $param
     * @return string
     */
    public function getFunctionParameterType(ReflectionParameter $param)
    {
        return $param->getType();
    }

    /**
     * Return function return type
     *
     * @param  AbstractFunction $function
     * @param  Prototype        $prototype
     * @return string
     */
    public function getFunctionReturnType(AbstractFunction $function, Prototype $prototype)
    {
        return $prototype->getReturnType();
    }

    /**
     * Return true if function is one way (return nothing)
     *
     * @param  AbstractFunction $function
     * @param  Prototype        $prototype
     * @return bool
     */
    public function isFunctionOneWay(AbstractFunction $function, Prototype $prototype)
    {
        return $prototype->getReturnType() == 'void';
    }
}
