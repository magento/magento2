<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Config;

use Laminas\Code\Reflection\ClassReflection;
use Laminas\Code\Reflection\MethodReflection;
use Magento\Framework\Reflection\TypeProcessor;

/**
 * Config class reflector
 */
class ClassReflector
{
    /**
     * @var TypeProcessor
     */
    protected $_typeProcessor;

    /**
     * @param TypeProcessor $typeProcessor
     */
    public function __construct(TypeProcessor $typeProcessor)
    {
        $this->_typeProcessor = $typeProcessor;
    }

    /**
     * Reflect methods in given class and set retrieved data into reader.
     *
     * @param string $className
     * @param string[]|array $methods List of methods of methods' metadata.
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
     * @throws \ReflectionException
     */
    public function reflectClassMethods($className, $methods)
    {
        $data = [];
        $classReflection = new ClassReflection($className);
        /** @var MethodReflection $methodReflection */
        foreach ($classReflection->getMethods() as $methodReflection) {
            $methodName = $methodReflection->getName();
            if (in_array($methodName, $methods) || array_key_exists($methodName, $methods)) {
                $data[$methodName] = $this->extractMethodData($methodReflection);
            }
        }
        return $data;
    }

    /**
     * Retrieve method interface and documentation description.
     *
     * @param MethodReflection $method
     * @return array
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     */
    public function extractMethodData(MethodReflection $method)
    {
        $methodData = ['documentation' => $this->extractMethodDescription($method), 'interface' => []];
        /** @var \Laminas\Code\Reflection\ParameterReflection $parameter */
        foreach ($method->getParameters() as $parameter) {
            $parameterData = [
                'type' => $this->_typeProcessor->register($this->_typeProcessor->getParamType($parameter)),
                'required' => !$parameter->isOptional(),
                'documentation' => $this->_typeProcessor->getParamDescription($parameter),
            ];
            if ($parameter->isOptional()) {
                $parameterData['default'] = $parameter->getDefaultValue();
            }
            $methodData['interface']['in']['parameters'][$parameter->getName()] = $parameterData;
        }
        $returnType = $this->_typeProcessor->getGetterReturnType($method);
        if ($returnType['type'] != 'void' && $returnType['type'] != 'null') {
            $methodData['interface']['out']['parameters']['result'] = [
                'type' => $this->_typeProcessor->register($returnType['type']),
                'documentation' => $returnType['description'],
                'required' => true,
            ];
        }
        $exceptions = $this->_typeProcessor->getExceptions($method);
        if (!empty($exceptions)) {
            $methodData['interface']['out']['throws'] = $exceptions;
        }

        return $methodData;
    }

    /**
     * Retrieve method full documentation description.
     *
     * @param MethodReflection $method
     * @return string
     * @throws \ReflectionException
     */
    protected function extractMethodDescription(MethodReflection $method)
    {
        $methodReflection = new MethodReflection(
            $method->getDeclaringClass()->getName(),
            $method->getName()
        );

        $docBlock = $methodReflection->getDocBlock();
        if (!$docBlock) {
            throw new \LogicException(
                'The docBlock of the method ' .
                $method->getDeclaringClass()->getName() . '::' . $method->getName() . ' is empty.'
            );
        }
        return $this->_typeProcessor->getDescription($docBlock);
    }

    /**
     * Retrieve class full documentation description.
     *
     * @param string $className
     * @return string
     * @throws \ReflectionException
     */
    public function extractClassDescription($className)
    {
        $classReflection = new ClassReflection($className);
        $docBlock = $classReflection->getDocBlock();
        if (!$docBlock) {
            return '';
        }
        return $this->_typeProcessor->getDescription($docBlock);
    }
}
