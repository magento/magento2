<?php
/**
 * Checks that all Exceptions inherit LocalizedException
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
namespace Magento\Test\Integrity;

use Magento\Framework\App\Utility\Files as UtilityFiles;

class ExceptionHierarchyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param \ReflectionClass $reflectionException
     * @dataProvider isInheritedLocalisedExceptionDataProvider
     */
    public function testIsInheritedLocalisedException(\ReflectionClass $reflectionException)
    {
        $this->assertTrue(
            $reflectionException->isSubclassOf('Magento\Framework\Exception\LocalizedException'),
            "{$reflectionException->name} is not inherited LocalisedException"
        );
    }

    /**
     * @return array
     */
    public function isInheritedLocalisedExceptionDataProvider()
    {
        $files = UtilityFiles::init()->getClassFiles(true, false, false, true, false);
        $blacklistExceptions = $this->isInheritedLocalisedExceptionBlacklist();

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
        $className = str_replace('.php', "", substr($filePath, strpos($filePath, '/Magento')));
        $className = implode("\\", explode("/", $className));
        return $className;
    }

    /**
     * @return array
     */
    protected static function isInheritedLocalisedExceptionBlacklist()
    {
        $blacklistFiles = str_replace('\\', '/', realpath(__DIR__)) . '/_files/blacklist/exception_hierarchy*.txt';
        $exceptions = [];
        foreach (glob($blacklistFiles) as $fileName) {
            $exceptions = array_merge($exceptions, file($fileName, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES));
        }
        return $exceptions;
    }
}
