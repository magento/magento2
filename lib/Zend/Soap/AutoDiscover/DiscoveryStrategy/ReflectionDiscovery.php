<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Soap
 */

namespace Zend\Soap\AutoDiscover\DiscoveryStrategy;

use Zend\Server\Reflection\AbstractFunction;
use Zend\Server\Reflection\Prototype;
use Zend\Server\Reflection\ReflectionParameter;

/**
 * Describes how types, return values and method details are detected during AutoDiscovery of a WSDL.
 *
 * @category   Zend
 * @package    Zend_Soap
 * @subpackage WSDL
 */

class ReflectionDiscovery implements DiscoveryStrategyInterface
{
    public function getFunctionDocumentation(AbstractFunction $function)
    {
        return $function->getDescription();
    }

    public function getFunctionParameterType(ReflectionParameter $param)
    {
        return $param->getType();
    }

    public function getFunctionReturnType(AbstractFunction $function, Prototype $prototype)
    {
        return $prototype->getReturnType();
    }

    public function isFunctionOneWay(AbstractFunction $function, Prototype $prototype)
    {
        return $prototype->getReturnType() == 'void';
    }
}
