<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Api;

/**
 * Factory class for instantiation of extension attributes objects.
 */
class ExtensionAttributesFactory
{
    public const EXTENSIBLE_INTERFACE_NAME = \Magento\Framework\Api\ExtensibleDataInterface::class;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Map is used for performance optimization.
     *
     * @var array
     */
    private $classInterfaceMap = [];

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create extension attributes object, custom for each extensible class.
     *
     * @param string $extensibleClassName
     * @param array $data
     * @return \Magento\Framework\Api\ExtensionAttributesInterface
     */
    public function create($extensibleClassName, $data = [])
    {
        $interfaceReflection = new \ReflectionClass($this->getExtensibleInterfaceName($extensibleClassName));

        $methodReflection = $interfaceReflection->getMethod('getExtensionAttributes');
        if ($methodReflection->getDeclaringClass()->getName() === self::EXTENSIBLE_INTERFACE_NAME) {
            throw new \LogicException(
                "Method 'getExtensionAttributes' must be overridden in the interfaces "
                . "which extend '" . self::EXTENSIBLE_INTERFACE_NAME . "'. "
                . "Concrete return type should be specified."
            );
        }

        $interfaceName = '\\' . $interfaceReflection->getName();
        $extensionClassName = substr($interfaceName, 0, -strlen('Interface')) . 'Extension';
        $extensionInterfaceName = $extensionClassName . 'Interface';

        /** Ensure that proper return type of getExtensionAttributes() method is specified */
        $methodDocBlock = (string) $methodReflection->getDocComment();
        $pattern = "/@return\s+" . str_replace('\\', '\\\\', $extensionInterfaceName) . "/";
        $hasExpectedReturnType = preg_match($pattern, $methodDocBlock);
        if (!$hasExpectedReturnType && !preg_match('/@return\b/', $methodDocBlock)) {
            $returnType = $methodReflection->getReturnType();
            $hasExpectedReturnType = $returnType instanceof \ReflectionNamedType
                && ltrim($returnType->getName(), '\\') === ltrim($extensionInterfaceName, '\\');
        }
        if (!$hasExpectedReturnType) {
            throw new \LogicException(
                "Method 'getExtensionAttributes' must be overridden in the interfaces "
                . "which extend '" . self::EXTENSIBLE_INTERFACE_NAME . "'. "
                . "Concrete return type must be specified. Please fix :" . $interfaceName
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
     */
    public function getExtensibleInterfaceName($extensibleClassName)
    {
        $exceptionMessage = "Class '{$extensibleClassName}' must implement an interface, "
            . "which extends from '" . self::EXTENSIBLE_INTERFACE_NAME . "'";
        $notExtensibleClassFlag = '';

        if (isset($this->classInterfaceMap[$extensibleClassName])) {
            if ($notExtensibleClassFlag === $this->classInterfaceMap[$extensibleClassName]) {
                throw new \LogicException($exceptionMessage);
            } else {
                return $this->classInterfaceMap[$extensibleClassName];
            }
        }
        $modelReflection = new \ReflectionClass($extensibleClassName);
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
        throw new \LogicException($exceptionMessage);
    }
}
