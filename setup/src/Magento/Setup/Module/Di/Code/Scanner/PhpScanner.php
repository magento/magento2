<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Code\Scanner;

use Magento\Framework\Api\Code\Generator\ExtensionAttributesGenerator;
use Magento\Framework\Api\Code\Generator\ExtensionAttributesInterfaceGenerator;
use Magento\Framework\ObjectManager\Code\Generator\Factory as FactoryGenerator;
use Magento\Setup\Module\Di\Compiler\Log\Log;

/**
 * Class \Magento\Setup\Module\Di\Code\Scanner\PhpScanner
 *
 */
class PhpScanner implements ScannerInterface
{
    /**
     * @var Log $log
     */
    protected $_log;

    /**
     * @param Log $log
     */
    public function __construct(Log $log)
    {
        $this->_log = $log;
    }

    /**
     * Find classes which are used as parameters types of the specified method and are not declared.
     *
     * @param string $file
     * @param \ReflectionClass $classReflection
     * @param string $methodName
     * @param string $entityType
     * @return string[]
     */
    protected function _findMissingClasses($file, $classReflection, $methodName, $entityType)
    {
        $missingClasses = [];
        if ($classReflection->hasMethod($methodName)) {
            $constructor = $classReflection->getMethod($methodName);
            $parameters = $constructor->getParameters();
            /** @var $parameter \ReflectionParameter */
            foreach ($parameters as $parameter) {
                preg_match('/\[\s\<\w+?>\s([\w\\\\]+)/s', $parameter->__toString(), $matches);
                if (isset($matches[1]) && substr($matches[1], -strlen($entityType)) == $entityType) {
                    $missingClassName = $matches[1];
                    try {
                        if (class_exists($missingClassName)) {
                            continue;
                        }
                    } catch (\RuntimeException $e) {
                    }
                    $sourceClassName = $this->getSourceClassName($missingClassName, $entityType);
                    if (!class_exists($sourceClassName) && !interface_exists($sourceClassName)) {
                        $this->_log->add(
                            Log::CONFIGURATION_ERROR,
                            $missingClassName,
                            "Invalid {$entityType} for nonexistent class {$sourceClassName} in file {$file}"
                        );
                        continue;
                    }
                    $missingClasses[] = $missingClassName;
                }
            }
        }
        return $missingClasses;
    }

    /**
     * Identify source class name for the provided class.
     *
     * @param string $missingClassName
     * @param string $entityType
     * @return string
     */
    protected function getSourceClassName($missingClassName, $entityType)
    {
        $sourceClassName = rtrim(substr($missingClassName, 0, -strlen($entityType)), '\\');
        $entityType = lcfirst($entityType);
        if ($entityType == ExtensionAttributesInterfaceGenerator::ENTITY_TYPE
            || $entityType == ExtensionAttributesGenerator::ENTITY_TYPE
        ) {
            /** Process special cases for extension class and extension interface */
            return $sourceClassName . 'Interface';
        } elseif ($entityType == FactoryGenerator::ENTITY_TYPE) {
            $extensionAttributesSuffix = ucfirst(ExtensionAttributesGenerator::ENTITY_TYPE);
            if (substr($sourceClassName, -strlen($extensionAttributesSuffix)) == $extensionAttributesSuffix) {
                /** Process special case for extension factories */
                $extensionAttributesClass = substr(
                    $sourceClassName,
                    0,
                    -strlen(ExtensionAttributesGenerator::ENTITY_TYPE)
                );
                $sourceClassName = $extensionAttributesClass . 'Interface';
            }
        }
        return $sourceClassName;
    }

    /**
     * Fetch factories from class constructor
     *
     * @param \ReflectionClass $reflectionClass
     * @param string $file
     * @return string[]
     */
    protected function _fetchFactories($reflectionClass, $file)
    {
        $factorySuffix = '\\' . ucfirst(FactoryGenerator::ENTITY_TYPE);
        $absentFactories = $this->_findMissingClasses(
            $file,
            $reflectionClass,
            '__construct',
            ucfirst(FactoryGenerator::ENTITY_TYPE)
        );
        foreach ($absentFactories as $key => $absentFactory) {
            if (substr($absentFactory, -strlen($factorySuffix)) == $factorySuffix) {
                $entityName = rtrim(substr($absentFactory, 0, -strlen($factorySuffix)), '\\');
                $this->_log->add(
                    Log::CONFIGURATION_ERROR,
                    $absentFactory,
                    'Invalid Factory declaration for class ' . $entityName . ' in file ' . $file
                );
                unset($absentFactories[$key]);
            }
        }
        return $absentFactories;
    }

    /**
     * Find missing extension attributes related classes, interfaces and factories.
     *
     * @param \ReflectionClass $reflectionClass
     * @param string $file
     * @return string[]
     */
    protected function _fetchMissingExtensionAttributesClasses($reflectionClass, $file)
    {
        $missingExtensionInterfaces = $this->_findMissingClasses(
            $file,
            $reflectionClass,
            'setExtensionAttributes',
            ucfirst(\Magento\Framework\Api\Code\Generator\ExtensionAttributesInterfaceGenerator::ENTITY_TYPE)
        );
        $missingExtensionClasses = [];
        $missingExtensionFactories = [];
        foreach ($missingExtensionInterfaces as $missingExtensionInterface) {
            $extension = rtrim(substr($missingExtensionInterface, 0, -strlen('Interface')), '\\');
            if (!class_exists($extension)) {
                $missingExtensionClasses[] = $extension;
            }
            $extensionFactory = $extension . 'Factory';
            if (!class_exists($extensionFactory)) {
                $missingExtensionFactories[] = $extensionFactory;
            }
        }
        return array_merge($missingExtensionInterfaces, $missingExtensionClasses, $missingExtensionFactories);
    }

    /**
     * Get array of class names
     *
     * @param array $files
     * @return array
     */
    public function collectEntities(array $files)
    {
        $output = [];
        foreach ($files as $file) {
            $classes = $this->_getDeclaredClasses($file);
            foreach ($classes as $className) {
                $reflectionClass = new \ReflectionClass($className);
                $output = array_merge(
                    $output,
                    $this->_fetchFactories($reflectionClass, $file),
                    $this->_fetchMissingExtensionAttributesClasses($reflectionClass, $file)
                );
            }
        }
        return array_unique($output);
    }

    /**
     * @param $tokenIterator int
     * @param $count int
     * @param $tokens array
     * @return string
     */
    protected function _fetchNamespace($tokenIterator, $count, $tokens)
    {
        $namespace = '';
        for ($tokenOffset = $tokenIterator + 1; $tokenOffset < $count; ++$tokenOffset) {
            if ($tokens[$tokenOffset][0] === T_STRING) {
                $namespace .= "\\" . $tokens[$tokenOffset][1];
            } elseif ($tokens[$tokenOffset] === '{' || $tokens[$tokenOffset] === ';') {
                break;
            }
        }
        return $namespace;
    }

    /**
     * @param $namespace string
     * @param $tokenIterator int
     * @param $count int
     * @param $tokens array
     * @return array
     */
    protected function _fetchClasses($namespace, $tokenIterator, $count, $tokens)
    {
        $classes = [];
        for ($tokenOffset = $tokenIterator + 1; $tokenOffset < $count; ++$tokenOffset) {
            if ($tokens[$tokenOffset] === '{') {
                $classes[] = $namespace . "\\" . $tokens[$tokenIterator + 2][1];
            }
        }
        return $classes;
    }

    /**
     * Get classes and interfaces declared in the file
     *
     * @param string $file
     * @return array
     */
    protected function _getDeclaredClasses($file)
    {
        $classes = [];
        $namespace = '';
        $tokens = token_get_all(file_get_contents($file));
        $count = count($tokens);

        for ($tokenIterator = 0; $tokenIterator < $count; $tokenIterator++) {
            if ($tokens[$tokenIterator][0] == T_NAMESPACE) {
                $namespace .= $this->_fetchNamespace($tokenIterator, $count, $tokens);
            }

            if (($tokens[$tokenIterator][0] == T_CLASS || $tokens[$tokenIterator][0] == T_INTERFACE)
                && $tokens[$tokenIterator - 1][0] != T_DOUBLE_COLON
            ) {
                $classes = array_merge($classes, $this->_fetchClasses($namespace, $tokenIterator, $count, $tokens));
            }
        }
        return array_unique($classes);
    }
}
