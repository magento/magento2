<?php
/**
 *  Copyright © Magento, Inc. All rights reserved.
 *  See COPYING.txt for license details.
 */

namespace Magento\Framework\TestFramework\Unit\Autoloader;

/**
 * Code generation for the undeclared *Extension and *ExtensionInterface types
 *
 * These files must be generated since they are referenced in many interfaces/classes and cannot be mocked easily.
 * For unit tests, these are just empty type definitions. You should use integration tests if you want to see the real
 * types be generated with the properties from the extension attributes config.
 */
class ExtensionAttributesGenerator implements GeneratorInterface
{
    /**
     * Generates a stub class/interface for classes that follow the convention
     *
     * The convention is "<SourceClass>ExtensionInterface" or "<SourceClass>Extension"
     *
     * @param string $className
     * @return bool|string
     */
    public function generate($className)
    {
        if (!$this->isExtension($className) && !$this->isExtensionInterface($className)) {
            return false;
        }
        $classNameParts = explode('\\', $className);

        /* Split the type name and namespace for the file's contents. */
        $justTypeName = $classNameParts[count($classNameParts) - 1];

        unset($classNameParts[count($classNameParts) - 1]);
        $namespace = implode('\\', $classNameParts);

        $content = false;
        if ($this->isExtension($className)) {
            $content = "namespace $namespace;\n\nclass $justTypeName implements "
                . "{$justTypeName}Interface\n{\n\n}";
        } elseif ($this->isExtensionInterface($className)) {
            $content = "namespace $namespace;\n\ninterface $justTypeName extends "
                . "\\Magento\\Framework\\Api\\ExtensionAttributesInterface \n{\n\n}";
        }
        return $content;
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
        return substr($className, -strlen($suffix), strlen($suffix)) === $suffix;
    }

    /**
     * Determines if the passed in class name is an Extension type.
     *
     * @param string $className
     * @return bool
     */
    private function isExtension($className)
    {
        $suffix = "Extension";
        return substr($className, -strlen($suffix), strlen($suffix)) === $suffix;
    }
}
