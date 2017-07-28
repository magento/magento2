<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Factory class for instantiation of extension attributes objects.
 * @since 2.0.0
 */
class ExtensionAttributesFactory
{
    const EXTENSIBLE_INTERFACE_NAME = \Magento\Framework\Api\ExtensibleDataInterface::class;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * Map is used for performance optimization.
     *
     * @var array
     * @since 2.0.0
     */
    private $classInterfaceMap = [];

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
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
     * @return object
     * @since 2.0.0
     */
    public function create($extensibleClassName, $data = [])
    {
        $interfaceReflection = new \ReflectionClass($this->getExtensibleInterfaceName($extensibleClassName));

        $methodReflection = $interfaceReflection->getMethod('getExtensionAttributes');
        if ($methodReflection->getDeclaringClass() == self::EXTENSIBLE_INTERFACE_NAME) {
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
        $methodDocBlock = $methodReflection->getDocComment();
        $pattern = "/@return\s+" . str_replace('\\', '\\\\', $extensionInterfaceName) . "/";
        if (!preg_match($pattern, $methodDocBlock)) {
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
     * @since 2.0.0
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
            && $modelReflection->isSubClassOf(self::EXTENSIBLE_INTERFACE_NAME)
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
