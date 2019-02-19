<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Magento\Framework\Api;

use Magento\Framework\App\Utility\Files;

/**
 * Check interfaces inherited from \Magento\Framework\Api\ExtensibleDataInterface.
 *
 * Ensure that all interfaces inherited from \Magento\Framework\Api\ExtensibleDataInterface
 * override getExtensionAttributes() method and have correct return type specified.
 */
class ExtensibleInterfacesTest extends \PHPUnit\Framework\TestCase
{
    const EXTENSIBLE_DATA_INTERFACE = \Magento\Framework\Api\ExtensibleDataInterface::class;

    /**
     * Check return types of getExtensionAttributes() methods.
     */
    public function testGetSetExtensionAttributes()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $filename
             */
            function ($filename) {
                $errors = [];
                $fileContent = file_get_contents($filename);
                $pattern = '/'
                    . str_replace('\\', '\\\\', self::EXTENSIBLE_DATA_INTERFACE)
                    . '/';
                $extendsFromExtensibleDataInterface = preg_match($pattern, $fileContent);
                $namespacePattern = '/namespace ([\w\\\\]+).*interface ([\w\\\\]+)/s';
                if ($extendsFromExtensibleDataInterface
                    && preg_match($namespacePattern, $fileContent, $matches)
                ) {
                    $namespace = $matches[1];
                    $interfaceName = $matches[2];
                    $fullInterfaceName = '\\' . $namespace . '\\' . $interfaceName;
                    $interfaceReflection = new \ReflectionClass($fullInterfaceName);
                    if ($interfaceReflection->isSubclassOf(self::EXTENSIBLE_DATA_INTERFACE)) {
                        $interfaceName = '\\' . $interfaceReflection->getName();
                        $extensionClassName = substr($interfaceName, 0, -strlen('Interface')) . 'Extension';
                        $extensionInterfaceName = $extensionClassName . 'Interface';

                        /** Check getExtensionAttributes method */
                        $errors = $this->checkGetExtensionAttributes(
                            $interfaceReflection,
                            $extensionInterfaceName,
                            $fullInterfaceName
                        );

                        /** Check setExtensionAttributes method */
                        $errors = array_merge(
                            $errors,
                            $this->checkSetExtensionAttributes(
                                $interfaceReflection,
                                $extensionInterfaceName,
                                $fullInterfaceName
                            )
                        );
                    }
                }

                $this->assertEmpty(
                    $errors,
                    "Error validating $filename\n" . print_r($errors, true)
                );
            },
            $this->getInterfacesFiles()
        );
    }

    /**
     * Check getExtensionAttributes methods
     *
     * @param \ReflectionClass $interfaceReflection
     * @param string $extensionInterfaceName
     * @param string $fullInterfaceName
     * @return array
     */
    private function checkGetExtensionAttributes(
        \ReflectionClass $interfaceReflection,
        $extensionInterfaceName,
        $fullInterfaceName
    ) {
        $errors = [];
        try {
            $methodReflection = $interfaceReflection->getMethod('getExtensionAttributes');
            /** Ensure that proper return type of getExtensionAttributes() method is specified */
            $methodDocBlock = $methodReflection->getDocComment();
            $pattern = "/@return\s+" . str_replace('\\', '\\\\', $extensionInterfaceName) . "/";
            if (!preg_match($pattern, $methodDocBlock)) {
                $errors[] =
                    "'{$fullInterfaceName}::getExtensionAttributes()' must be declared "
                    . "with a return type of '{$extensionInterfaceName}'.";
            }
        } catch (\ReflectionException $e) {
            $errors[] = "The following method should be declared in "
                . "'{$extensionInterfaceName}'. '{$extensionInterfaceName}' must be specified as"
                . " a return type for '{$fullInterfaceName}::getExtensionAttributes()'";
        }

        return $errors;
    }

    /**
     * Check setExtensionAttributes methods
     *
     * @param \ReflectionClass $interfaceReflection
     * @param string $extensionInterfaceName
     * @param string $fullInterfaceName
     * @return array
     */
    private function checkSetExtensionAttributes(
        \ReflectionClass $interfaceReflection,
        $extensionInterfaceName,
        $fullInterfaceName
    ) {
        $errors = [];
        try {
            $methodReflection = $interfaceReflection->getMethod('setExtensionAttributes');
            /** Ensure that proper argument type for setExtensionAttributes() method is specified */
            $methodParameters = $methodReflection->getParameters();

            if (empty($methodParameters)) {
                $errors[] = "'{$extensionInterfaceName}' must be specified as the parameter type "
                    . "in '{$fullInterfaceName}::setExtensionAttributes()'.";
            } else {
                // Get the parameter name via a regular expression capture because the class may
                // not exist which causes a fatal error
                preg_match('/\[\s\<\w+?>\s([\w]+)/s', $methodParameters[0]->__toString(), $matches);
                $isCorrectParameter = false;
                if (isset($matches[1]) && '\\' . $matches[1] != $extensionInterfaceName) {
                    $isCorrectParameter = true;
                }

                if (!$isCorrectParameter) {
                    $errors[] = "'{$extensionInterfaceName}' must be specified as the parameter type "
                        . "in '{$fullInterfaceName}::setExtensionAttributes()'.";
                }
            }
        } catch (\ReflectionException $e) {
            $errors[] = "'{$fullInterfaceName}::setExtensionAttributes()' must be declared "
                . "with a '{$extensionInterfaceName}' parameter type.";
        }

        return $errors;
    }

    /**
     * Ensure that all classes extended from extensible classes implement getter and setter for extension attributes.
     */
    public function testExtensibleClassesWithMissingInterface()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $filename
             */
            function ($filename) {
                $errors = [];
                $fileContent = file_get_contents($filename);
                $extensibleClassPattern = 'class [^\{]+extends[^\{]+AbstractExtensible';
                $abstractExtensibleClassPattern = 'abstract ' . $extensibleClassPattern;
                if (preg_match('/' . $extensibleClassPattern . '/', $fileContent) &&
                    !preg_match('/' . $abstractExtensibleClassPattern . '/', $fileContent)
                ) {
                    $fileReflection = new \Zend\Code\Reflection\FileReflection($filename, true);
                    foreach ($fileReflection->getClasses() as $classReflection) {
                        if ($classReflection->isSubclassOf(self::EXTENSIBLE_DATA_INTERFACE)) {
                            $methodsToCheck = ['setExtensionAttributes', 'getExtensionAttributes'];
                            foreach ($methodsToCheck as $methodName) {
                                try {
                                    $classReflection->getMethod($methodName);
                                } catch (\ReflectionException $e) {
                                    $className = $classReflection->getName();
                                    $errors[] = "'{$className}::{$methodName}()' must be declared or "
                                        . "'{$className}' should not be inherited from extensible class.";
                                }
                            }
                        }
                    }
                }

                $this->assertEmpty(
                    $errors,
                    "Error validating $filename\n" . print_r($errors, true)
                );
            },
            $this->getPhpFiles()
        );
    }

    /**
     * Retrieve a list of all interfaces declared in the Magento application and Magento library.
     *
     * @return array
     */
    public function getInterfacesFiles()
    {
        $codeInterfaceFiles = $this->getFiles(BP . '/app', '*Interface.php');
        $libInterfaceFiles = $this->getFiles(BP . '/lib/Magento', '*Interface.php');
        $interfaces = [];
        $filesToCheck = $this->blacklistFilter(array_merge($codeInterfaceFiles, $libInterfaceFiles));
        foreach ($filesToCheck as $file) {
            $interfaces[substr($file, strlen(BP))] = [$file];
        }
        return $interfaces;
    }

    /**
     * Retrieve a list of all php files declared in the Magento application and Magento library.
     *
     * @return array
     */
    public function getPhpFiles()
    {
        $codeFiles = $this->getFiles(BP . '/app', '*.php');
        $libFiles = $this->getFiles(BP . '/lib/Magento', '*.php');
        $phpFiles = [];
        $filesToCheck = $this->blacklistFilter(array_merge($codeFiles, $libFiles));
        foreach ($filesToCheck as $file) {
            $phpFiles[substr($file, strlen(BP))] = [$file];
        }
        return $phpFiles;
    }

    /**
     * Retrieve all files in a directory that correspond to the given pattern
     *
     * @param string $dir
     * @param string $pattern
     * @return array
     */
    protected function getFiles($dir, $pattern)
    {
        $files = glob($dir . '/' . $pattern, GLOB_NOSORT);
        foreach (glob($dir . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $newDir) {
            $files = array_merge($files, $this->getFiles($newDir, $pattern));
        }
        return $files;
    }

    /**
     * Filter blacklisted files out of an array
     *
     * @param array $preFilter
     * @return array
     */
    protected function blacklistFilter($preFilter)
    {
        $postFilter = [];
        $blacklist = Files::init()->readLists(__DIR__ . '/_files/ExtensibleInterfacesTest/blacklist*');
        foreach ($preFilter as $file) {
            if (!in_array($file, $blacklist)) {
                $postFilter[] = $file;
            }
        }
        return $postFilter;
    }
}
