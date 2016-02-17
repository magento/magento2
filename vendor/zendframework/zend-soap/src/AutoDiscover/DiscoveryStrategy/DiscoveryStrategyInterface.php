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
interface DiscoveryStrategyInterface
{
    /**
     * Get the function parameters php type.
     *
     * Default implementation assumes the default param doc-block tag.
     *
     * @param  ReflectionParameter $param
     * @return string
     */
    public function getFunctionParameterType(ReflectionParameter $param);

    /**
     * Get the functions return php type.
     *
     * Default implementation assumes the value of the return doc-block tag.
     *
     * @param  AbstractFunction $function
     * @param  Prototype $prototype
     * @return string
     */
    public function getFunctionReturnType(AbstractFunction $function, Prototype $prototype);

    /**
     * Detect if the function is a one-way or two-way operation.
     *
     * Default implementation assumes one-way, when return value is "void".
     *
     * @param  AbstractFunction $function
     * @param  Prototype $prototype
     * @return bool
     */
    public function isFunctionOneWay(AbstractFunction $function, Prototype $prototype);

    /**
     * Detect the functions documentation.
     *
     * Default implementation uses docblock description.
     *
     * @param  AbstractFunction $function
     * @return string
     */
    public function getFunctionDocumentation(AbstractFunction $function);
}
