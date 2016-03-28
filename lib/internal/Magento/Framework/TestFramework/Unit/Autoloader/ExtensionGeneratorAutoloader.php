<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\TestFramework\Unit\Autoloader;

/**
 * Enable code generation for the undeclared *Extension and *ExtensionInterface types.
 *
 * These files must be generated since they are referenced in many interfaces/classes and cannot be mocked easily.
 * For unit tests, these are just empty type definitions. You should use integration tests if you want to see the real
 * types be generated with the properties from the extension attributes config.
 */
class ExtensionGeneratorAutoloader
{
    /**
     * @var \Magento\Framework\Code\Generator\Io
     */
    private $generatorIo;

    /**
     * @param \Magento\Framework\Code\Generator\Io $generatorIo
     */
    public function __construct($generatorIo)
    {
        $this->generatorIo = $generatorIo;
    }

    /**
     * Load an *Extension or *ExtensionInterface class. If it does not exist, create a stub file and load it.
     *
     * @param string $className
     * @return void
     */
    public function load($className)
    {
        if (!class_exists($className)) {
            if (!$this->isExtension($className) && !$this->isExtensionInterface($className)) {
                return false;
            }

            $resultFileName = $this->generatorIo->generateResultFileName($className);

            if (!$this->generatorIo->fileExists($resultFileName)) {
                $this->generatorIo->makeResultFileDirectory($className);

                $classNameParts = explode('\\', $className);

                /* Split the type name and namespace for the file's contents. */
                $justTypeName = $classNameParts[count($classNameParts) - 1];

                unset($classNameParts[count($classNameParts) - 1]);
                $namespace = implode('\\', $classNameParts);

                if ($this->isExtension($className)) {
                    $content = "namespace $namespace;\n\nclass $justTypeName implements "
                        . "{$justTypeName}Interface\n{\n\n}";
                } else if ($this->isExtensionInterface($className)) {
                    $content = "namespace $namespace;\n\ninterface $justTypeName extends "
                        . "\\Magento\\Framework\\Api\\ExtensionAttributesInterface \n{\n\n}";
                }

                $this->generatorIo->writeResultFile($resultFileName, $content);
            }

            include $resultFileName;
        }

        return false;
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
