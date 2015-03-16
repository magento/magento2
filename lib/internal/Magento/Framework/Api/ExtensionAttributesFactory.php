<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Factory class for instantiation of extension attributes objects.
 */
class ExtensionAttributesFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create extension attributes object, custom for each extensible class.
     *
     * @param string $extensibleClassName
     * @param array $data
     * @return object
     */
    public function create($extensibleClassName, $data = [])
    {
        $modelReflection = new \ReflectionClass($extensibleClassName);

        $implementsExtensibleInterface = false;
        $extensibleInterfaceName = 'Magento\Framework\Api\ExtensibleDataInterface';
        foreach ($modelReflection->getInterfaces() as $interfaceReflection) {
            if ($interfaceReflection->isSubclassOf($extensibleInterfaceName)
                && $interfaceReflection->hasMethod('getExtensionAttributes')
            ) {
                $implementsExtensibleInterface = true;
                break;
            }
        }
        if (!$implementsExtensibleInterface) {
            throw new \LogicException(
                "Class '{$extensibleClassName}' must implement an interface, "
                . "which extends from 'Magento\\Framework\\Api\\ExtensibleDataInterface'"
            );
        }

        $methodReflection = $interfaceReflection->getMethod('getExtensionAttributes');
        if ($methodReflection->getDeclaringClass() == $extensibleInterfaceName) {
            throw new \LogicException(
                "Method 'getExtensionAttributes' must be overridden in the interfaces "
                . "which extend 'Magento\\Framework\\Api\\ExtensibleDataInterface'. "
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
                . "which extend 'Magento\\Framework\\Api\\ExtensibleDataInterface'. "
                . "Concrete return type must be specified. Please fix :" . $interfaceName
            );
        }

        $extensionFactoryName = $extensionClassName . 'Factory';
        $extensionFactory = $this->_objectManager->create($extensionFactoryName);
        return $extensionFactory->create($data);
    }
}
