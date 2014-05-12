<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Js;

/**
 * Duplicating the same namespace in the "use" below is a workaround to comply with
 * \Magento\Test\Integrity\ClassesTest::testClassReferences()
 */
use Magento\TestFramework\Utility\Files;
use Magento\TestFramework\Utility\AggregateInvoker;

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
    protected static $_whiteListJsFiles = array();

    /**
     * @var array
     */
    protected static $_blackListJsFiles = array();

    /**
     * @static Return all files under a path
     * @param string $path
     * @return array
     */
    protected static function _scanJsFile($path)
    {
        if (is_file($path)) {
            return array($path);
        }
        $path = $path == '' ? __DIR__ : $path;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        $regexIterator = new \RegexIterator($iterator, '/\\.js$/');
        $filePaths = array();
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
            mkdir($reportDir, 0777);
        }
        self::$_reportFile = $reportDir . '/js_report.txt';
        @unlink(self::$_reportFile);
        $whiteList = Files::readLists(__DIR__ . '/_files/whitelist/*.txt');
        $blackList = Files::readLists(__DIR__ . '/_files/blacklist/*.txt');
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
                    $this->assertTrue($cmd->run(array()), $cmd->getLastRunMessage());
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
            return array($value);
        };
        return array_map($map, self::$_whiteListJsFiles);
    }
}
