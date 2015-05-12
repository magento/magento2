<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Enable code generation for the undeclared *Extension and *ExtensionInterface types. These files must be generated
 * since they are referenced in many interfaces/classes and cannot be mocked easily. For unit tests, these are just
 * empty type definitions. You should use integration tests if you want to see the real types be generated with
 * the properties from the extension attributes config.
 */
class GeneratorAutoloader
{
    /**
     * @var \Magento\Framework\Code\Generator\Io
     */
    private $generatorIo;

    public function __construct()
    {
        $this->generatorIo = new \Magento\Framework\Code\Generator\Io(
            new \Magento\Framework\Filesystem\Driver\File(),
            TESTS_TEMP_DIR . '/var/generation'
        );
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

            $resultFileName = $this->generatorIo->getResultFileName($className);

            if (!$this->generatorIo->fileExists($resultFileName)) {
                $this->generatorIo->makeResultFileDirectory($className);

                $classNameParts = explode('\\', $className);

                /* Split the type name and namespace for the file's contents. */
                $justTypeName = $classNameParts[count($classNameParts) - 1];

                unset($classNameParts[count($classNameParts) - 1]);
                $namespace = implode('\\', $classNameParts);

                if ($this->isExtension($className)) {
                    $content = "namespace $namespace;\n\nclass $justTypeName implements {$justTypeName}Interface\n{\n\n}";
                } else if ($this->isExtensionInterface($className)) {
                    $content = "namespace $namespace;\n\ninterface $justTypeName extends \\Magento\\Framework\\Api\\ExtensionAttributesInterface \n{\n\n}";
                }

                $this->generatorIo->writeResultFile($resultFileName, $content);
            }

            include $resultFileName;
        }

        return false;
    }

    /**
     * @param string $className
     * @return bool
     */
    private function isExtensionInterface($className)
    {
        $suffix = "ExtensionInterface";
        return substr($className, -strlen($suffix), strlen($suffix)) === $suffix;
    }

    /**
     * @param string $className
     * @return bool
     */
    private function isExtension($className)
    {
        $suffix = "Extension";
        return substr($className, -strlen($suffix), strlen($suffix)) === $suffix;
    }
}

$autoloader = new GeneratorAutoloader();
spl_autoload_register([$autoloader, 'load']);
