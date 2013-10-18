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
 * @category    tests
 * @package     static
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * JSHint static code analysis tests for javascript files
 */
namespace Magento\Test\Js;

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
        $reportDir = \Magento\TestFramework\Utility\Files::init()->getPathToSource() . '/dev/tests/static/report';
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0777);
        }
        self::$_reportFile = $reportDir . '/js_report.txt';
        @unlink(self::$_reportFile);
        $whiteList = self::_readLists(__DIR__ . '/_files/whitelist/*.txt');
        $blackList = self::_readLists(__DIR__ . '/_files/blacklist/*.txt');
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
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
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

    /**
     * Read all text files by specified glob pattern and combine them into an array of valid files/directories
     *
     * The Magento root path is prepended to all (non-empty) entries
     *
     * @param string $globPattern
     * @return array
     */
    protected static function _readLists($globPattern)
    {
        $result = array();
        foreach (glob($globPattern) as $list) {
            $result = array_merge($result, file($list));
        }
        $map = function ($value) {
            return trim($value) ?
                \Magento\TestFramework\Utility\Files::init()->getPathToSource() . DIRECTORY_SEPARATOR .
                str_replace('/', DIRECTORY_SEPARATOR, trim($value)) : '';
        };
        return array_filter(array_map($map, $result), 'file_exists');
    }
}
