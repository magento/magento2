<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests to find usage of restricted code
 */
namespace Magento\Test\Legacy;

class RestrictedCodeTest extends \PHPUnit_Framework_TestCase
{
    /**@#+
     * Lists of restricted entities from fixtures
     *
     * @var array
     */
    protected static $_classes = [];
    /**#@-*/

    /**
     * List of fixtures that contain restricted classes and should not be tested
     * @var array
     */
    protected static $_fixtureFiles = [];

    /**
     * Read fixtures into memory as arrays
     * @return void
     */
    public static function setUpBeforeClass()
    {
        self::_loadData(self::$_classes, 'restricted_classes*.php');
    }

    /**
     * Loads and merges data from fixtures
     *
     * @param array $data
     * @param string $filePattern
     * @return void
     */
    protected static function _loadData(array &$data, $filePattern)
    {
        foreach (glob(__DIR__ . '/_files/' . $filePattern) as $file) {
            $relativePath = str_replace(
                '\\',
                '/',
                str_replace(\Magento\Framework\App\Utility\Files::init()->getPathToSource(), '', $file)
            );
            array_push(self::$_fixtureFiles, $relativePath);
            $data = array_merge_recursive($data, self::_readList($file));
        }
    }

    /**
     * Isolate including a file into a method to reduce scope
     *
     * @param string $file
     * @return array
     */
    protected static function _readList($file)
    {
        return include $file;
    }

    /**
     * Test that restricted entities are not used in PHP files
     * @return void
     */
    public function testPhpFiles()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $testFiles = \Magento\TestFramework\Utility\ChangedFiles::getPhpFiles(__DIR__ . '/_files/changed_files*');
        foreach (self::$_fixtureFiles as $fixtureFile) {
            if (array_key_exists($fixtureFile, $testFiles)) {
                unset($testFiles[$fixtureFile]);
            }
        }
        $invoker(
            function ($file) {
                $this->_testRestrictedClasses($file);
            },
            $testFiles
        );
    }

    /**
     * Assert that restricted classes are not used in the file
     *
     * @param string $file
     * @return void
     */
    protected function _testRestrictedClasses($file)
    {
        $content = file_get_contents($file);
        foreach (self::$_classes as $restrictedClass => $classRules) {
            foreach ($classRules['exclude'] as $skippedPath) {
                if ($this->_isFileInPath($skippedPath, $file)) {
                    continue 2;
                }
            }
            $this->assertFalse(
                \Magento\TestFramework\Utility\CodeCheck::isClassUsed($restrictedClass, $content),
                sprintf(
                    "Class '%s' is restricted. Suggested replacement: %s",
                    $restrictedClass,
                    $classRules['replacement']
                )
            );
        }
    }

    /**
     * Checks if the file path (relative to Magento folder) starts with the given path
     *
     * @param string $subPath
     * @param string $file
     * @return bool
     */
    protected function _isFileInPath($subPath, $file)
    {
        $relativePath = str_replace(\Magento\Framework\App\Utility\Files::init()->getPathToSource(), '', $file);
        return 0 === strpos($relativePath, $subPath);
    }
}
