<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

use LogicException;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Reflection\TypeProcessor;
use ReflectionClass;
use ReflectionException;
use Zend\Code\Reflection\ClassReflection;

/**
 * Factory class for instantiation of extension attributes objects.
 */
class ExtensionAttributesFactory
{
    /**
     * Extensible interface name constant
     */
    public const EXTENSIBLE_INTERFACE_NAME = ExtensibleDataInterface::class;

    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Map is used for performance optimization.
     *
     * @var array
     */
    private $classInterfaceMap = [];

    /**
     * @var TypeProcessor
     */
    private $typeProcessor;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param TypeProcessor|null $typeProcessor
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        TypeProcessor $typeProcessor = null
    ) {
        $this->objectManager = $objectManager;
        $this->typeProcessor = $typeProcessor ?: ObjectManager::getInstance()
            ->get(TypeProcessor::class);
    }

    /**
     * Create extension attributes object, custom for each extensible class.
     *
     * @param string $extensibleClassName
     * @param array $data
     * @return ExtensionAttributesInterface
     * @throws ReflectionException
     */
    public function create($extensibleClassName, $data = [])
    {
        $interfaceReflection = new ClassReflection($this->getExtensibleInterfaceName($extensibleClassName));

        $methodReflection = $interfaceReflection->getMethod('getExtensionAttributes');
        if ($methodReflection->getDeclaringClass()->getName() === self::EXTENSIBLE_INTERFACE_NAME) {
            throw new LogicException(
                "Method 'getExtensionAttributes' must be overridden in the interfaces "
                . "which extend '" . self::EXTENSIBLE_INTERFACE_NAME . "'. "
            );
        }

        $interfaceName = '\\' . $interfaceReflection->getName();
        $extensionClassName = substr($interfaceName, 0, -strlen('Interface')) . 'Extension';

        /** Ensure that proper return type of getExtensionAttributes() method is specified */
        $methodDocBlock = $methodReflection->getDocComment();
        if (!preg_match('/@return\s+([\w\\\\]+)/', $methodDocBlock, $matches)) {
            throw new LogicException(
                "Method 'getExtensionAttributes' must specify a return value."
            );
        }

        $extensionInterfaceName = $this->typeProcessor
            ->resolveFullyQualifiedClassName($interfaceReflection, $extensionClassName . 'Interface');

        $returnExtensionInterfaceName = $this->typeProcessor
            ->resolveFullyQualifiedClassName($interfaceReflection, $matches[1]);

        if ($returnExtensionInterfaceName !== $extensionInterfaceName) {
            throw new LogicException(
                "Method 'getExtensionAttributes' must return $returnExtensionInterfaceName"
            );
        }

        $extensionFactoryName = $extensionClassName . 'Factory';
        $extensionFactory = $this->objectManager->create($extensionFactoryName);
        return $extensionFactory->create($data);
    }

    /**
     * Identify concrete extensible interface name based on the class name.
     *
     * @param string $extensibleClassName
     * @return string
     * @throws ReflectionException
     */
    public function getExtensibleInterfaceName($extensibleClassName)
    {
        $exceptionMessage = "Class '{$extensibleClassName}' must implement an interface, "
            . "which extends from '" . self::EXTENSIBLE_INTERFACE_NAME . "'";
        $notExtensibleClassFlag = '';

        if (isset($this->classInterfaceMap[$extensibleClassName])) {
            if ($notExtensibleClassFlag === $this->classInterfaceMap[$extensibleClassName]) {
                throw new LogicException($exceptionMessage);
            }

            return $this->classInterfaceMap[$extensibleClassName];
        }
        $modelReflection = new ReflectionClass($extensibleClassName);
        if ($modelReflection->isInterface()
            && $modelReflection->isSubclassOf(self::EXTENSIBLE_INTERFACE_NAME)
            && $modelReflection->hasMethod('getExtensionAttributes')
        ) {
            $this->classInterfaceMap[$extensibleClassName] = $extensibleClassName;
            return $this->classInterfaceMap[$extensibleClassName];
        }
        foreach ($modelReflection->getInterfaces() as $interfaceReflection) {
            if ($interfaceReflection->isSubclassOf(self::EXTENSIBLE_INTERFACE_NAME)
                && $interfaceReflection->hasMethod('getExtensionAttributes')
            ) {
                $this->classInterfaceMap[$extensibleClassName] = $interfaceReflection->getName();
                return $this->classInterfaceMap[$extensibleClassName];
            }
        }
        $this->classInterfaceMap[$extensibleClassName] = $notExtensibleClassFlag;
        throw new LogicException($exceptionMessage);
    }
}
