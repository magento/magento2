<?php
/**
 *  Copyright © Magento, Inc. All rights reserved.
 *  See COPYING.txt for license details.
 */

namespace Magento\Framework\TestFramework\Unit\Autoloader;

use Magento\Framework\Code\Generator\ClassGenerator;

/**
 * Code generation for the undeclared *Extension types
 *
 * These files must be generated since they are referenced in many interfaces/classes and cannot be mocked easily.
 * For unit tests, these are just empty type definitions. You should use integration tests if you want to see the real
 * types be generated with the properties from the extension attributes config.
 */
class ExtensionAttributesGenerator implements GeneratorInterface
{
    /**
     * Generates a stub class for classes that follow the convention
     *
     * The convention is "<SourceClass>Extension"
     *
     * @param string $className
     * @return bool|string
     */
    public function generate($className)
    {
        if (!$this->isExtension($className)) {
            return false;
        }
        $classGenerator = new ClassGenerator();
        $classGenerator->setName($className)
            ->setImplementedInterfaces(["{$className}Interface"]);
        return $classGenerator->generate();
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
        $sourceName = $className !== null ? rtrim(substr($className, 0, -strlen($suffix)), '\\') : '';
        return $sourceName . $suffix == $className;
    }
}
