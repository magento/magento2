<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Config;

use Zend\Server\Reflection;
use Zend\Server\Reflection\ReflectionMethod;
use Zend\Code\Reflection\MethodReflection;

/**
 * Class reflector.
 */
class ClassReflector
{
    /**
     * @var \Magento\Framework\Reflection\TypeProcessor
     */
    protected $_typeProcessor;

    /**
     * Construct reflector.
     *
     * @param \Magento\Framework\Reflection\TypeProcessor $typeProcessor
     */
    public function __construct(\Magento\Framework\Reflection\TypeProcessor $typeProcessor)
    {
        $this->_typeProcessor = $typeProcessor;
    }

    /**
     * Reflect methods in given class and set retrieved data into reader.
     *
     * @param string $className
     * @param array $methods
     * @return array <pre>array(
     *     $firstMethod => array(
     *         'documentation' => $methodDocumentation,
     *         'interface' => array(
     *             'in' => array(
     *                 'parameters' => array(
     *                     $firstParameter => array(
     *                         'type' => $type,
     *                         'required' => $isRequired,
     *                         'documentation' => $parameterDocumentation
     *                     ),
     *                     ...
     *                 )
     *             ),
     *             'out' => array(
     *                 'parameters' => array(
     *                     $firstParameter => array(
     *                         'type' => $type,
     *                         'required' => $isRequired,
     *                         'documentation' => $parameterDocumentation
     *                     ),
     *                     ...
     *                 )
     *             )
     *         )
     *     ),
     *     ...
     * )</pre>
     */
    public function reflectClassMethods($className, $methods)
    {
        $data = [];
        $classReflection = new \Zend\Server\Reflection\ReflectionClass(new \ReflectionClass($className));
        /** @var $methodReflection ReflectionMethod */
        foreach ($classReflection->getMethods() as $methodReflection) {
            $methodName = $methodReflection->getName();
            if (array_key_exists($methodName, $methods)) {
                $data[$methodName] = $this->extractMethodData($methodReflection);
            }
        }
        return $data;
    }

    /**
     * Retrieve method interface and documentation description.
     *
     * @param ReflectionMethod $method
     * @return array
     * @throws \InvalidArgumentException
     */
    public function extractMethodData(ReflectionMethod $method)
    {
        $methodData = ['documentation' => $this->extractMethodDescription($method), 'interface' => []];
        $prototypes = $method->getPrototypes();
        /** Take the fullest interface that also includes optional parameters. */
        /** @var \Zend\Server\Reflection\Prototype $prototype */
        $prototype = end($prototypes);
        /** @var \Zend\Server\Reflection\ReflectionParameter $parameter */
        foreach ($prototype->getParameters() as $parameter) {
            $parameterData = [
                'type' => $this->_typeProcessor->process($parameter->getType()),
                'required' => !$parameter->isOptional(),
                'documentation' => $parameter->getDescription(),
            ];
            if ($parameter->isOptional()) {
                $parameterData['default'] = $parameter->getDefaultValue();
            }
            $methodData['interface']['in']['parameters'][$parameter->getName()] = $parameterData;
        }
        if ($prototype->getReturnType() != 'void' && $prototype->getReturnType() != 'null') {
            $methodData['interface']['out']['parameters']['result'] = [
                'type' => $this->_typeProcessor->process($prototype->getReturnType()),
                'documentation' => $prototype->getReturnValue()->getDescription(),
                'required' => true,
            ];
        }

        return $methodData;
    }

    /**
     * Retrieve method full documentation description.
     *
     * @param ReflectionMethod $method
     * @return string
     */
    protected function extractMethodDescription(ReflectionMethod $method)
    {
        $methodReflection = new MethodReflection(
            $method->getDeclaringClass()->getName(),
            $method->getName()
        );

        $docBlock = $methodReflection->getDocBlock();

        return $this->_typeProcessor->getDescription($docBlock);
    }
}
