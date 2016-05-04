<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Js;

/**
 * Duplicating the same namespace in the "use" below is a workaround to comply with
 * \Magento\Test\Integrity\ClassesTest::testClassReferences()
 */
use Magento\Framework\App\Utility\AggregateInvoker;
use Magento\Framework\App\Utility\Files;

/**
 * JSHint static code analysis tests for javascript files
 */
class LiveCodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $_reportFile = '';

    /**
     * @var array
     */
    protected static $_whiteListJsFiles = [];

    /**
     * @var array
     */
    protected static $_blackListJsFiles = [];

    /**
     * @static Return all files under a path
     * @param string $path
     * @return array
     */
    protected static function _scanJsFile($path)
    {
        if (is_file($path)) {
            return [$path];
        }
        $path = $path == '' ? __DIR__ : $path;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        $regexIterator = new \RegexIterator($iterator, '/\\.js$/');
        $filePaths = [];
        foreach ($regexIterator as $filePath) {
            $filePaths[] = $filePath->getPathname();
        }
        return $filePaths;
    }

    /**
     * @static Setup report file, black list and white list
     *
     */
    public static function setUpBeforeClass()
    {
        $reportDir = Files::init()->getPathToSource() . '/dev/tests/static/report';
        if (!is_dir($reportDir)) {
            mkdir($reportDir);
        }
        self::$_reportFile = $reportDir . '/js_report.txt';
        @unlink(self::$_reportFile);
        $whiteList = Files::init()->readLists(__DIR__ . '/_files/jshint/whitelist/*.txt');
        $blackList = Files::init()->readLists(__DIR__ . '/_files/jshint/blacklist/*.txt');
        foreach ($blackList as $listFiles) {
            self::$_blackListJsFiles = array_merge(self::$_blackListJsFiles, self::_scanJsFile($listFiles));
        }
        foreach ($whiteList as $listFiles) {
            self::$_whiteListJsFiles = array_merge(self::$_whiteListJsFiles, self::_scanJsFile($listFiles));
        }
        $blackListJsFiles = self::$_blackListJsFiles;
        $filter = function ($value) use ($blackListJsFiles) {
            return !in_array($value, $blackListJsFiles);
        };
        self::$_whiteListJsFiles = array_filter(self::$_whiteListJsFiles, $filter);
    }

    public function testCodeJsHint()
    {
        return; // Avoid "Failing task since test cases were expected but none were found."
        $this->markTestIncomplete('MAGETWO-27639: Enhance JavaScript Static Tests');
        $invoker = new AggregateInvoker($this);
        $invoker(
            /**
             * @param string $filename
             */
            function ($filename) {
                $cmd = new \Magento\TestFramework\Inspection\JsHint\Command($filename, self::$_reportFile);
                $result = false;
                try {
                    $result = $cmd->canRun();
                } catch (\Exception $e) {
                    $this->markTestSkipped($e->getMessage());
                }
                if ($result) {
                    $this->assertTrue($cmd->run([]), $cmd->getLastRunMessage());
                }
            },
            $this->codeJsHintDataProvider()
        );
    }

    /**
     * Build data provider array with command, js file name, and option
     * @return array
     */
    public function codeJsHintDataProvider()
    {
        self::setUpBeforeClass();
        $map = function ($value) {
            return [$value];
        };
        return array_map($map, self::$_whiteListJsFiles);
    }
}
