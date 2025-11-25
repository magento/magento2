<?php
/**
 * Copyright 2012 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\TestFramework\Unit\Helper;

use Magento\Framework\GetParameterClassTrait;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ObjectManager as AppObjectManager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Helper class for basic object retrieving, such as blocks, models etc...
 *
 * @deprecated Class under test should be instantiated with `new` keyword with explicit dependencies declaration
 * @see https://github.com/magento/magento2/pull/29272
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ObjectManager
{
    use GetParameterClassTrait;

    /**
     * Special cases configuration
     *
     * @var array
     */
    protected $_specialCases = [
        \Magento\Framework\Model\ResourceModel\AbstractResource::class => '_getResourceModelMock',
        \Magento\Framework\TranslateInterface::class => '_getTranslatorMock',
    ];

    /**
     * @var \PHPUnit\Framework\TestCase
     */
    protected $_testObject;

    /**
     * Constructor
     *
     * @param \PHPUnit\Framework\TestCase $testObject
     */
    public function __construct(\PHPUnit\Framework\TestCase $testObject)
    {
        $this->_testObject = $testObject;
    }

    /**
     * Get mock for argument
     *
     * @param string $argClassName
     * @param array $originalArguments
     * @return null|object|MockObject
     */
    protected function _createArgumentMock($argClassName, array $originalArguments)
    {
        $object = null;
        if ($argClassName) {
            $object = $this->_processSpecialCases($argClassName, $originalArguments);
            if (null === $object) {
                $object = $this->_getMockWithoutConstructorCall($argClassName);
            }
        }
        return $object;
    }

    /**
     * Process special cases
     *
     * @param string $className
     * @param array $arguments
     * @return null|object
     */
    protected function _processSpecialCases($className, $arguments)
    {
        $object = null;
        $interfaces = class_implements($className);
        if (in_array(\Magento\Framework\ObjectManager\ContextInterface::class, $interfaces)) {
            $object = $this->getObject($className, $arguments);
        } elseif (isset($this->_specialCases[$className])) {
            $method = $this->_specialCases[$className];
            $object = $this->{$method}($className);
        }

        return $object;
    }

    /**
     * Retrieve specific mock of core resource model
     *
     * @return \Magento\Framework\Module\ResourceInterface|MockObject
     */
    protected function _getResourceModelMock()
    {
        $reflection = new \ReflectionClass($this->_testObject);
        $method = $reflection->getMethod('createPartialMock');
        $method->setAccessible(true);
        $resourceMock = $method->invoke(
            $this->_testObject,
            \Magento\Framework\Module\ModuleResource::class,
            ['getIdFieldName', '__sleep', '__wakeup']
        );
        $reflection = new \ReflectionClass($this->_testObject);
        $anyMethod = $reflection->getMethod('any');
        $anyMethod->setAccessible(true);
        $resourceMock->expects(
            $anyMethod->invoke($this->_testObject)
        )->method(
            'getIdFieldName'
        )->willReturn('id');

        return $resourceMock;
    }

    /**
     * Retrieve mock of core translator model
     *
     * @param string $className
     * @return \Magento\Framework\TranslateInterface|MockObject
     */
    protected function _getTranslatorMock($className)
    {
        $reflection = new \ReflectionClass($this->_testObject);
        $method = $reflection->getMethod('createMock');
        $method->setAccessible(true);
        $translator = $method->invoke($this->_testObject, $className);
        $translateCallback = function ($arguments) {
            return is_array($arguments) ? vsprintf(array_shift($arguments), $arguments) : '';
        };
        $reflection = new \ReflectionClass($this->_testObject);
        $anyMethod = $reflection->getMethod('any');
        $anyMethod->setAccessible(true);
        $translator->expects(
            $anyMethod->invoke($this->_testObject)
        )->method(
            'translate'
        )->willReturnCallback($translateCallback);
        return $translator;
    }

    /**
     * Get mock without call of original constructor
     *
     * @param string $className
     * @return MockObject
     */
    protected function _getMockWithoutConstructorCall($className)
    {
        // Use reflection to call protected createMock method
        $reflection = new \ReflectionClass($this->_testObject);
        $method = $reflection->getMethod('createMock');
        $method->setAccessible(true);
        return $method->invoke($this->_testObject, $className);
    }

    /**
     * Get class instance
     *
     * @param string $className
     * @param array $arguments
     * @return object
     */
    public function getObject($className, array $arguments = [])
    {
        if (is_subclass_of($className, \Magento\Framework\Api\AbstractSimpleObjectBuilder::class)) {
            return $this->getBuilder($className, $arguments);
        }
        $constructArguments = $this->getConstructArguments($className, $arguments);
        $reflectionClass = new \ReflectionClass($className);
        $newObject = $reflectionClass->newInstanceArgs($constructArguments);

        foreach (array_diff_key($arguments, $constructArguments) as $key => $value) {
            $propertyReflectionClass = $reflectionClass;
            while ($propertyReflectionClass) {
                if ($propertyReflectionClass->hasProperty($key)) {
                    $reflectionProperty = $propertyReflectionClass->getProperty($key);
                    $reflectionProperty->setAccessible(true);
                    $reflectionProperty->setValue($newObject, $value);
                    break;
                }
                $propertyReflectionClass = $propertyReflectionClass->getParentClass();
            }
        }
        return $newObject;
    }

    /**
     * Get data object builder
     *
     * @param string $className
     * @param array $arguments
     * @return object
     */
    protected function getBuilder($className, array $arguments)
    {
        if (!isset($arguments['objectFactory'])) {
            $reflection = new \ReflectionClass($this->_testObject);
            $method = $reflection->getMethod('getMockBuilder');
            $method->setAccessible(true);
            $mockBuilder = $method->invoke($this->_testObject, \Magento\Framework\Api\ObjectFactory::class);
            
            // Use onlyMethods() with methods that actually exist in ObjectFactory
            $objectFactory = $mockBuilder->onlyMethods(['create', 'get'])
                ->disableOriginalConstructor()
                ->getMock();

            $reflection = new \ReflectionClass($this->_testObject);
            $anyMethod = $reflection->getMethod('any');
            $anyMethod->setAccessible(true);
            
            // Only configure methods that actually exist in ObjectFactory
            $objectFactory->expects($anyMethod->invoke($this->_testObject))
                ->method('create')
                ->willReturnCallback(
                    function ($className, $arguments) {
                        $reflectionClass = new \ReflectionClass($className);
                        $constructorMethod = $reflectionClass->getConstructor();
                        $parameters = $constructorMethod->getParameters();
                        $args = [];
                        foreach ($parameters as $parameter) {
                            $parameterName = $parameter->getName();
                            if (isset($arguments[$parameterName])) {
                                $args[] = $arguments[$parameterName];
                            } else {
                                if ($parameter->getType() && $parameter->getType()->getName() === 'array') {
                                    $args[] = [];
                                } elseif ($parameter->allowsNull()) {
                                    $args[] = null;
                                } else {
                                    $parameterClass = $this->getParameterClass($parameter);
                                    $mock = $this->_getMockWithoutConstructorCall($parameterClass->getName());
                                    $args[] = $mock;
                                }
                            }
                        }
                        return new $className(...array_values($args));
                    }
                );
            
            $objectFactory->expects($anyMethod->invoke($this->_testObject))
                ->method('get')
                ->willReturnCallback(
                    function ($className) {
                        return $this->_getMockWithoutConstructorCall($className);
                    }
                );

            $arguments['objectFactory'] = $objectFactory;
        }

        return new $className(...array_values($this->getConstructArguments($className, $arguments)));
    }

    /**
     * Retrieve associative array of arguments that used for new object instance creation
     *
     * @param string $className
     * @param array $arguments
     * @return array
     */
    public function getConstructArguments($className, array $arguments = [])
    {
        $constructArguments = [];
        if (!method_exists($className, '__construct')) {
            return $constructArguments;
        }
        $method = new \ReflectionMethod($className, '__construct');

        foreach ($method->getParameters() as $parameter) {
            $parameterName = $parameter->getName();
            $argClassName = null;
            $defaultValue = null;

            if (array_key_exists($parameterName, $arguments)) {
                $constructArguments[$parameterName] = $arguments[$parameterName];
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $defaultValue = $parameter->getDefaultValue();
            }

            $object = null;
            try {
                if ($parameterClass = $this->getParameterClass($parameter)) {
                    $argClassName = $parameterClass->getName();
                }
                $object = $this->_getMockObject($argClassName, $arguments);
            } catch (\ReflectionException $e) {
                $parameterString = $parameter->__toString();
                $firstPosition = strpos($parameterString, '<required>');
                if ($firstPosition !== false) {
                    $parameterString = substr($parameterString, $firstPosition + 11);
                    $parameterString = substr($parameterString, 0, strpos($parameterString, ' '));
                    $reflection = new \ReflectionClass($this->_testObject);
                    $method = $reflection->getMethod('createMock');
                    $method->setAccessible(true);
                    $object = $method->invoke($this->_testObject, $parameterString);
                }
            }

            $constructArguments[$parameterName] = null === $object ? $defaultValue : $object;
        }
        return $constructArguments;
    }

    /**
     * Get collection mock
     *
     * @param string $className
     * @param array $data
     * @return MockObject
     * @throws \InvalidArgumentException
     */
    public function getCollectionMock($className, array $data)
    {
        if (!is_subclass_of($className, \Magento\Framework\Data\Collection::class)) {
            throw new \InvalidArgumentException(
                $className . ' does not instance of \Magento\Framework\Data\Collection'
            );
        }
        $reflection = new \ReflectionClass($this->_testObject);
        $method = $reflection->getMethod('createMock');
        $method->setAccessible(true);
        $mock = $method->invoke($this->_testObject, $className);
        $iterator = new \ArrayIterator($data);
        $reflection = new \ReflectionClass($this->_testObject);
        $anyMethod = $reflection->getMethod('any');
        $anyMethod->setAccessible(true);
        $mock->expects(
            $anyMethod->invoke($this->_testObject)
        )->method(
            'getIterator'
        )->willReturn($iterator);
        return $mock;
    }

    /**
     * Helper function that creates a mock object for a given class name.
     *
     * Will return a real object in some cases to assist in testing.
     *
     * @param string $argClassName
     * @param array $arguments
     * @return null|object|MockObject
     */
    private function _getMockObject($argClassName, array $arguments)
    {
        // phpstan:ignore
        if (is_subclass_of($argClassName, \Magento\Framework\Api\ExtensibleObjectBuilder::class)) {
            return $this->getBuilder($argClassName, $arguments);
        }

        return $this->_createArgumentMock($argClassName, $arguments);
    }

    /**
     * Set mocked property
     *
     * @param object $object
     * @param string $propertyName
     * @param object $propertyValue
     * @param string $className The namespace of parent class for injection private property into this class
     * @return void
     */
    public function setBackwardCompatibleProperty($object, $propertyName, $propertyValue, $className = '')
    {
        $reflection = new \ReflectionClass($className ? $className : get_class($object));
        $reflectionProperty = $reflection->getProperty($propertyName);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $propertyValue);
    }

    /**
     * Helper method to get mock of ObjectManagerInterface
     *
     * @param array $map
     */
    public function prepareObjectManager(array $map = [])
    {
        $reflection = new \ReflectionClass($this->_testObject);
        $method = $reflection->getMethod('createMock');
        $method->setAccessible(true);
        $objectManagerMock = $method->invoke(
            $this->_testObject,
            ObjectManagerInterface::class
        );

        $objectManagerMock->method('get')->willReturnMap($map);

        $reflectionProperty = new \ReflectionProperty(AppObjectManager::class, '_instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($objectManagerMock, $objectManagerMock);
    }

    /**
     * Create a partial mock with reflection.
     *
     * @param string $className
     * @param array $methods
     * @return MockObject
     */
    public function createPartialMockWithReflection(string $className, array $methods): MockObject
    {
        $reflection = new \ReflectionClass($this->_testObject);
        $getMockBuilderMethod = $reflection->getMethod('getMockBuilder');
        $getMockBuilderMethod->setAccessible(true);
        $mockBuilder = $getMockBuilderMethod->invoke($this->_testObject, $className);
        
        $builderReflection = new \ReflectionClass($mockBuilder);
        $methodsProperty = $builderReflection->getProperty('methods');
        $methodsProperty->setAccessible(true);
        $methodsProperty->setValue($mockBuilder, $methods);
        
        $mockBuilder->disableOriginalConstructor();
        return $mockBuilder->getMock();
    }
}
