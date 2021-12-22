<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Workaround for decreasing memory consumption by cleaning up static properties
 */
namespace Magento\TestFramework\Workaround\Cleanup;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Resets static properties of classes before each test run
 */
class StaticProperties
{
    /**
     * Directories to clear static variables.
     *
     * Format: ['cleanableFolder' => ['pseudo-globs to match uncleanable subfolders']]
     *
     * @var array
     */
    protected static $_cleanableFolders = [
        '/dev/tests/integration/framework' => [],
    ];

    protected static $backupStaticVariables = [];

    /**
     * Classes to exclude from static variables cleaning
     *
     * @var array
     */
    protected static $_classesToSkip = [
        \Magento\Framework\App\ObjectManager::class,
        \Magento\TestFramework\Helper\Bootstrap::class,
        \Magento\TestFramework\Event\Magento::class,
        \Magento\TestFramework\Event\PhpUnit::class,
        \Magento\TestFramework\Annotation\AppIsolation::class,
        \Magento\TestFramework\Workaround\Cleanup\StaticProperties::class,
        \Magento\Framework\Phrase::class,
        \Magento\TestFramework\Workaround\Override\Fixture\ResolverInterface::class,
        \Magento\TestFramework\Workaround\Override\ConfigInterface::class,
    ];

    private const CACHE_NAME = 'integration_test_static_properties';

    /**
     * Constructor
     */
    public function __construct()
    {
        $componentRegistrar = new ComponentRegistrar();
        /** @var \Magento\Framework\Filesystem $filesystem */
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleDir) {
            $key = $moduleDir . '/';
            $value = $key . 'Test/Unit/';
            self::$_cleanableFolders[$key] = [$value];
        }
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::LIBRARY) as $libraryDir) {
            $key = $libraryDir . '/';
            $valueRootFolder = $key . '/Test/Unit/';
            $valueSubFolder = $key . '/*/Test/Unit/';
            self::$_cleanableFolders[$key] = [$valueSubFolder, $valueRootFolder];
        }
    }

    /**
     * Check whether it is allowed to clean given class static variables
     *
     * @param \ReflectionClass $reflectionClass
     * @return bool
     * phpcs:disable Magento2.Functions.StaticFunction
     */
    protected static function _isClassCleanable(\ReflectionClass $reflectionClass)
    {
        // do not process skipped classes from integration framework
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
     * phpcs:disable Magento2.Functions.StaticFunction
     */
    protected static function _isClassInCleanableFolders($classFile)
    {
        $classFile = str_replace('\\', '/', $classFile);
        foreach (self::$_cleanableFolders as $include => $excludeSet) {
            if (stripos($classFile, $include) !== false) {
                foreach ($excludeSet as $exclude) {
                    $excludeExp = '#' . str_replace('*', '[\w]+', $exclude) . '#';
                    if (preg_match($excludeExp, $classFile)) {
                        return false; // File is in an "include" directory, but also an "exclude" subdirectory of it
                    }
                }
                return true; // File is in an "include" directory, and not in an "exclude" subdirectory of it
            }
        }
        return false; // File is not in an "include" directory
    }

    /**
     * @var \ReflectionClass[]
     */
    protected static $classes = [];

    /**
     * Create a reflection class from the provided class
     *
     * @param string $class
     * @return \ReflectionClass
     * phpcs:disable Magento2.Functions.StaticFunction
     */
    private static function getReflectionClass($class)
    {
        if (!isset(self::$classes[$class])) {
            self::$classes[$class] = new \ReflectionClass($class);
        }
        return self::$classes[$class];
    }

    /**
     * Restore static variables (after running controller test case)
     *
     * @TODO: refactor all code where objects are stored to static variables to use object manager instead
     * phpcs:ignore Magento2.Functions.StaticFunction
     */
    public static function restoreStaticVariables()
    {
        foreach (array_keys(self::$backupStaticVariables) as $class) {
            $reflectionClass = self::getReflectionClass($class);
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * phpcs:disable Magento2.Functions.StaticFunction
     */
    public static function backupStaticVariables()
    {
        if (count(self::$backupStaticVariables) > 0) {
            return;
        }

        $objectManager = Bootstrap::getInstance()->getObjectManager();
        $cache = $objectManager->get(CacheInterface::class);
        $serializer = $objectManager->get(\Magento\TestFramework\Serialize\Serializer::class);
        $cachedProperties = $cache->load(self::CACHE_NAME);

        if ($cachedProperties) {
            self::$backupStaticVariables = $serializer->unserialize($cachedProperties);
            return;
        }

        unset($cachedProperties, $objectManager);

        $classFiles = array_filter(
            Files::init()->getPhpFiles(
                Files::INCLUDE_APP_CODE
                | Files::INCLUDE_LIBS
                | Files::INCLUDE_TESTS
            ),
            function ($classFile) {
                return strpos($classFile, 'TestFramework')  === -1
                    && StaticProperties::_isClassInCleanableFolders($classFile)
                    // phpcs:ignore Magento2.Functions.DiscouragedFunction
                    && strpos(file_get_contents($classFile), ' static ')  > 0;
            }
        );
        $namespacePattern = '/namespace [a-zA-Z0-9\\\\]+;/';
        $classPattern = '/\nclass [a-zA-Z0-9_]+/';
        foreach ($classFiles as $classFile) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $code = file_get_contents($classFile);
            preg_match($namespacePattern, $code, $namespace);
            preg_match($classPattern, $code, $class);
            if (!isset($namespace[0]) || !isset($class[0])) {
                continue;
            }
            // trim namespace and class name
            $namespace = substr($namespace[0], 10, strlen($namespace[0]) - 11);
            $class = substr($class[0], 7, strlen($class[0]) - 7);
            $className = $namespace . '\\' . $class;

            try {
                $reflectionClass = self::getReflectionClass($className);
            } catch (\Exception $e) {
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
        }

        $cache->save($serializer->serialize(self::$backupStaticVariables), self::CACHE_NAME);
    }

    /**
     * Handler for 'startTestSuite' event
     */
    public function startTestSuite()
    {
        self::backupStaticVariables();
    }

    /**
     * Handler for 'endTestSuite' event
     *
     * @param \PHPUnit\Framework\TestSuite $suite
     */
    public function endTestSuite(\PHPUnit\Framework\TestSuite $suite)
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
