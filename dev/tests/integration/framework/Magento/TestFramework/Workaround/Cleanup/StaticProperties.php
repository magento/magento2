<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Workaround for decreasing memory consumption by cleaning up static properties
 */
namespace Magento\TestFramework\Workaround\Cleanup;

class StaticProperties
{
    /**
     * Directories to clear static variables
     *
     * @var array
     */
    protected static $_cleanableFolders = ['/app/code/', '/dev/tests/integration/framework', '/lib/internal/'];

    protected static $backupStaticVariables = [];

    /**
     * Classes to exclude from static variables cleaning
     *
     * @var array
     */
    protected static $_classesToSkip = [
        'Mage',
        'Magento\Framework\App\ObjectManager',
        'Magento\TestFramework\Helper\Bootstrap',
        'Magento\TestFramework\Event\Magento',
        'Magento\TestFramework\Event\PhpUnit',
        'Magento\TestFramework\Annotation\AppIsolation',
        'Magento\TestFramework\Workaround\Cleanup\StaticProperties',
        'Magento\Framework\Phrase',
    ];

    /**
     * Check whether it is allowed to clean given class static variables
     *
     * @param \ReflectionClass $reflectionClass
     * @return bool
     */
    protected static function _isClassCleanable(\ReflectionClass $reflectionClass)
    {
        // do not process blacklisted classes from integration framework
        foreach (self::$_classesToSkip as $notCleanableClass) {
            if ($reflectionClass->getName() == $notCleanableClass || is_subclass_of(
                $reflectionClass->getName(),
                $notCleanableClass
            )
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if class has to be backed up
     *
     * @param string $classFile
     * @return bool
     */
    protected static function _isClassInCleanableFolders($classFile)
    {
        $classFile = str_replace('\\', '/', $classFile);
        foreach (self::$_cleanableFolders as $directory) {
            if (stripos($classFile, $directory) !== false) {
                return true;
            }
        }
        return false;
    }
    

    /**
     * Restore static variables (after running controller test case)
     * @TODO: refactor all code where objects are stored to static variables to use object manager instead
     */
    public static function restoreStaticVariables()
    {
        foreach (array_keys(self::$backupStaticVariables) as $class) {
            $reflectionClass = new \ReflectionClass($class);
            $staticProperties = $reflectionClass->getProperties(\ReflectionProperty::IS_STATIC);
            foreach ($staticProperties as $staticProperty) {
                $staticProperty->setAccessible(true);
                $staticProperty->setValue(self::$backupStaticVariables[$class][$staticProperty->getName()]);
            }
        }
    }

    /**
     * Backup static variables
     *
     */
    public static function backupStaticVariables()
    {
        $classFiles = \Magento\Framework\Test\Utility\Files::init()->getClassFiles(true, true, false, true, false);
        $namespacePattern = '/namespace [a-zA-Z0-9\\\\]+;/';
        $classPattern = '/\nclass [a-zA-Z0-9_]+/';
        foreach ($classFiles as $classFile) {
            if (self::_isClassInCleanableFolders($classFile)) {
                $file = @fopen($classFile, 'r');
                $code = fread($file, 4096);
                preg_match($namespacePattern, $code, $namespace);
                preg_match($classPattern, $code, $class);
                if (!isset($namespace[0]) || !isset($class[0])) {
                    fclose($file);
                    continue;
                }
                // trim namespace and class name
                $namespace = substr($namespace[0], 10, strlen($namespace[0]) - 11);
                $class = substr($class[0], 7, strlen($class[0]) - 7);
                $className = $namespace . '\\' . $class;

                try {
                    $reflectionClass = new \ReflectionClass($className);
                } catch (\Exception $e) {
                    fclose($file);
                    continue;
                }
                if (self::_isClassCleanable($reflectionClass)) {
                    $staticProperties = $reflectionClass->getProperties(\ReflectionProperty::IS_STATIC);
                    foreach ($staticProperties as $staticProperty) {
                        $staticProperty->setAccessible(true);
                        $value = $staticProperty->getValue();
                        self::$backupStaticVariables[$className][$staticProperty->getName()] = $value;
                    }
                }
                fclose($file);
            }
        }
    }

    /**
     * Handler for 'startTestSuite' event
     *
     */
    public function startTestSuite()
    {
        if (empty(self::$backupStaticVariables)) {
            self::backupStaticVariables();
        }
    }

    /**
     * Handler for 'endTestSuite' event
     *
     * @param \PHPUnit_Framework_TestSuite $suite
     */
    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $clearStatics = false;
        foreach ($suite->tests() as $test) {
            if ($test instanceof \Magento\TestFramework\TestCase\AbstractController) {
                $clearStatics = true;
                break;
            }
        }
        if ($clearStatics) {
            self::restoreStaticVariables();
        }
    }
}
