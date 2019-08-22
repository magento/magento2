<?php
/**
 *  Copyright © Magento, Inc. All rights reserved.
 *  See COPYING.txt for license details.
 */

namespace Magento\Framework\TestFramework\Unit\Autoloader;

use Magento\Framework\Code\Generator\InterfaceGenerator;

/**
 * Code generation for the undeclared *ExtensionInterface
 *
 * These files must be generated since they are referenced in many interfaces/classes and cannot be mocked easily.
 * For unit tests, these are just empty type definitions. You should use integration tests if you want to see the real
 * types be generated with the properties from the extension attributes config.
 */
class ExtensionAttributesInterfaceGenerator implements GeneratorInterface
{
    /**
     * Generates a stub interface for interfaces that follow the convention
     *
     * The convention is "<SourceClass>ExtensionInterface"
     *
     * @param string $className
     * @return bool|string
     */
    public function generate($className)
    {
        if (!$this->isExtensionInterface($className)) {
            return false;
        }
        $interfaceGenerator = new InterfaceGenerator();
        $interfaceGenerator->setName($className)
            ->setExtendedClass(\Magento\Framework\Api\ExtensionAttributesInterface::class);
        return $interfaceGenerator->generate();
    }

    /**
     * Determines if the passed in class name is an ExtensionInterface type.
     *
     * @param string $className
     * @return bool
     */
    private function isExtensionInterface($className)
    {
        $suffix = "ExtensionInterface";
        $sourceName = rtrim(substr($className, 0, -strlen($suffix)), '\\');
        return $sourceName . $suffix == $className;
    }
}
