<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Legacy;

use Magento\Framework\Component\ComponentRegistrar;

/**
 * Tests to find usage of restricted code
 */
class RestrictedCodeTest extends \PHPUnit_Framework_TestCase
{
    /**@#+
     * Lists of restricted entities from fixtures
     *
     * @var array
     */
    private static $_classes = [];
    /**#@-*/

    /**
     * List of fixtures that contain restricted classes and should not be tested
     *
     * @var array
     */
    private static $_fixtureFiles = [];

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    protected function setUp()
    {
        $this->componentRegistrar = new ComponentRegistrar();
    }


    /**
     * Read fixtures into memory as arrays
     *
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
                str_replace(BP, '', $file)
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
     *
     * @return void
     */
    public function testPhpFiles()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $testFiles = \Magento\TestFramework\Utility\ChangedFiles::getPhpFiles(__DIR__ . '/../_files/changed_files*');
        foreach (self::$_fixtureFiles as $fixtureFile) {
            if (array_key_exists(BP . $fixtureFile, $testFiles)) {
                unset($testFiles[BP . $fixtureFile]);
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
            foreach ($classRules['exclude'] as $skippedPathInfo) {
                if (strpos($file, $this->getExcludedFilePath($skippedPathInfo)) === 0) {
                    continue 2;
                }
            }

            $this->assertFalse(
                \Magento\TestFramework\Utility\CodeCheck::isClassUsed($restrictedClass, $content),
                sprintf(
                    "Class '%s' is restricted in %s. Suggested replacement: %s",
                    $restrictedClass,
                    $file,
                    $classRules['replacement']
                )
            );
        }
    }

    /**
     * Get full path for excluded file
     *
     * @param array $pathInfo
     * @return string
     */
    private function getExcludedFilePath($pathInfo)
    {
        if ($pathInfo['type'] != 'setup') {
            return $this->componentRegistrar->getPath($pathInfo['type'], $pathInfo['name']) . '/' . $pathInfo['path'];
        }
        return BP . '/setup/' . $pathInfo['path'];
    }
}
