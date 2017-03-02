<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity;

use Magento\Framework\App\Utility\Files as UtilityFiles;

/**
 * Checks that all Exceptions inherit LocalizedException
 */
class ExceptionHierarchyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param \ReflectionClass $reflectionException
     * @dataProvider isInheritedLocalizedExceptionDataProvider
     */
    public function testIsInheritedLocalizedException(\ReflectionClass $reflectionException)
    {
        $this->assertTrue(
            $reflectionException->isSubclassOf(\Magento\Framework\Exception\LocalizedException::class),
            "{$reflectionException->name} is not inherited LocalizedException"
        );
    }

    /**
     * @return array
     */
    public function isInheritedLocalizedExceptionDataProvider()
    {
        $files = UtilityFiles::init()->getPhpFiles(UtilityFiles::INCLUDE_APP_CODE | UtilityFiles::INCLUDE_LIBS);
        $blacklistExceptions = $this->getBlacklistExceptions();

        $data = [];
        foreach ($files as $file) {
            $className = $this->convertPathToClassName($file);
            try {
                $reflectionClass = new \ReflectionClass($className);
                if ($reflectionClass->isSubclassOf('Exception') && !in_array($className, $blacklistExceptions)) {
                    $data[$className] = [$reflectionClass];
                }
            } catch (\Exception $e) {
                $this->fail("File name and class name '{$className}' is not appropriate");
            }
        }
        return $data;
    }

    /**
     * @param string $filePath
     * @return string
     */
    protected function convertPathToClassName($filePath)
    {
        $componentRegistrar = new \Magento\Framework\Component\ComponentRegistrar();
        $foundItems = null;
        $moduleNamespace = null;
        $foundItems = array_filter(
            $componentRegistrar->getPaths(\Magento\Framework\Component\ComponentRegistrar::MODULE),
            function ($item) use ($filePath) {
                if (strpos($filePath, $item . '/') !== false) {
                    return true;
                } else {
                    return false;
                }
            }
        );
        if ($foundItems) {
            $moduleNamespace = str_replace('_', '\\', array_keys($foundItems)[0]);
            $classPath = str_replace('/', '\\', str_replace(array_shift($foundItems), '', $filePath));
        } else {
            $foundItems = array_filter(
                $componentRegistrar->getPaths(\Magento\Framework\Component\ComponentRegistrar::LIBRARY),
                function ($item) use ($filePath) {
                    if (strpos($filePath, $item . '/') !== false) {
                        return true;
                    } else {
                        return false;
                    }
                }
            );
            $libName = array_keys($foundItems)[0];
            $libName = str_replace('framework-', 'framework/', $libName);
            $namespaceParts = explode('/', $libName);
            $namespaceParts = array_map(
                function ($item) {
                    return str_replace(' ', '', ucwords(str_replace('-', ' ', $item)));
                },
                $namespaceParts
            );
            $moduleNamespace = implode('\\', $namespaceParts);
            $classPath = str_replace('/', '\\', str_replace(array_shift($foundItems), '', $filePath));
        }

        $className = '\\' . $moduleNamespace . $classPath;
        $className = str_replace('.php', '', $className);
        return $className;
    }

    /**
     * @return array
     */
    protected function getBlacklistExceptions()
    {
        $blacklistFiles = str_replace('\\', '/', realpath(__DIR__)) . '/_files/blacklist/exception_hierarchy*.txt';
        $exceptions = [];
        foreach (glob($blacklistFiles) as $fileName) {
            $exceptions = array_merge($exceptions, file($fileName, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES));
        }
        return $exceptions;
    }
}
