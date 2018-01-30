<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Test\Php;

use Magento\Framework\App\Utility;
use Magento\TestFramework\CodingStandard\Tool\CodeMessDetector;
use Magento\TestFramework\CodingStandard\Tool\CodeSniffer;
use Magento\TestFramework\CodingStandard\Tool\CodeSniffer\Wrapper;
use Magento\TestFramework\CodingStandard\Tool\CopyPasteDetector;
use PHPMD\TextUI\Command;
use PHPUnit_Framework_TestCase;
use Magento\Framework\App\Utility\Files;

/**
 * Set of tests for static code analysis, e.g. code style, code complexity, copy paste detecting, etc.
 */
class LiveCodeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $reportDir = '';

    /**
     * @var string
     */
    protected static $pathToSource = '';

    /**
     * Setup basics for all tests
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        self::$pathToSource = BP;
        self::$reportDir = self::$pathToSource . '/dev/tests/static/report';
        if (!is_dir(self::$reportDir)) {
            mkdir(self::$reportDir);
        }
    }

    /**
     * Returns base folder for suite scope
     *
     * @return string
     */
    private static function getBaseFilesFolder() {
        return __DIR__;
    }

    /**
     * Returns base directory for whitelisted files
     *
     * @return string
     */
    private static function getChangedFilesBaseDir() {
        return __DIR__ . '/..';
    }

    /**
     * Returns whitelist based on blacklist and git changed files
     *
     * @param array $fileTypes
     * @param string $changedFilesBaseDir
     * @param string $baseFilesFolder
     * @return array
     */
    public static function getWhitelist($fileTypes = ['php'], $changedFilesBaseDir = '', $baseFilesFolder = '')
    {
        $globPatternsFolder = self::getBaseFilesFolder();
        if ('' !== $baseFilesFolder) {
            $globPatternsFolder = $baseFilesFolder;
        }
        $directoriesToCheck = Files::init()->readLists($globPatternsFolder . '/_files/whitelist/common.txt');

        $changedFiles = [];
        $globFilesListPattern = ($changedFilesBaseDir ?: self::getChangedFilesBaseDir()) . '/_files/changed_files*';
        foreach (glob($globFilesListPattern) as $listFile) {
            $changedFiles = array_merge($changedFiles, file($listFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
        }
        array_walk(
            $changedFiles,
            function (&$file) {
                $file = BP . '/' . $file;
            }
        );
        $changedFiles = array_filter(
            $changedFiles,
            function ($path) use ($directoriesToCheck, $fileTypes) {
                if (!file_exists($path)) {
                    return false;
                }
                $path = realpath($path);
                foreach ($directoriesToCheck as $directory) {
                    $directory = realpath($directory);
                    if (strpos($path, $directory) === 0) {
                        if (!empty($fileTypes)) {
                            return in_array(pathinfo($path, PATHINFO_EXTENSION), $fileTypes);
                        }
                        return true;
                    }
                }
                return false;
            }
        );

        return $changedFiles;
    }

    /**
     * Run the PSR2 code sniffs on the code
     *
     * @TODO: combine with testCodeStyle
     * @return void
     */
    public function testCodeStylePsr2()
    {
        $reportFile = self::$reportDir . '/phpcs_psr2_report.txt';
        $wrapper = new Wrapper();
        $codeSniffer = new CodeSniffer('PSR2', $reportFile, $wrapper);
        if (!$codeSniffer->canRun()) {
            $this->markTestSkipped('PHP Code Sniffer is not installed.');
        }
        if (version_compare($wrapper->version(), '1.4.7') === -1) {
            $this->markTestSkipped('PHP Code Sniffer Build Too Old.');
        }

        $result = $codeSniffer->run(self::getWhitelist());

        $output = "";
        if (file_exists($reportFile)) {
            $output = file_get_contents($reportFile);
        }
        $this->assertEquals(
            0,
            $result,
            "PHP Code Sniffer has found {$result} error(s): " . PHP_EOL . $output
        );
    }

    /**
     * Run the magento specific coding standards on the code
     *
     * @return void
     */
    public function testCodeStyle()
    {
        $reportFile = self::$reportDir . '/phpcs_report.txt';
        $wrapper = new Wrapper();
        $codeSniffer = new CodeSniffer(realpath(__DIR__ . '/_files/phpcs'), $reportFile, $wrapper);
        if (!$codeSniffer->canRun()) {
            $this->markTestSkipped('PHP Code Sniffer is not installed.');
        }
        $codeSniffer->setExtensions(['php', 'phtml']);
        $result = $codeSniffer->run(self::getWhitelist(['php', 'phtml']));

        $output = "";
        if (file_exists($reportFile)) {
            $output = file_get_contents($reportFile);
        }

        $this->assertEquals(
            0,
            $result,
            "PHP Code Sniffer has found {$result} error(s): " . PHP_EOL . $output
        );
    }

    /**
     * Run the annotations sniffs on the code
     *
     * @return void
     * @todo Combine with normal code style at some point.
     */
    public function testAnnotationStandard()
    {
        $reportFile = self::$reportDir . '/phpcs_annotations_report.txt';
        $wrapper = new Wrapper();
        $codeSniffer = new CodeSniffer(
            realpath(__DIR__ . '/../../../../framework/Magento/ruleset.xml'),
            $reportFile,
            $wrapper
        );
        if (!$codeSniffer->canRun()) {
            $this->markTestSkipped('PHP Code Sniffer is not installed.');
        }

        $result = $codeSniffer->run(self::getWhitelist(['php']));
        $output = "";
        if (file_exists($reportFile)) {
            $output = file_get_contents($reportFile);
        }
        $this->assertEquals(
            0,
            $result,
            "PHP Code Sniffer has found {$result} error(s): " . PHP_EOL . $output
        );
    }

    /**
     * Run mess detector on code
     *
     * @return void
     */
    public function testCodeMess()
    {
        $reportFile = self::$reportDir . '/phpmd_report.txt';
        $codeMessDetector = new CodeMessDetector(realpath(__DIR__ . '/_files/phpmd/ruleset.xml'), $reportFile);

        if (!$codeMessDetector->canRun()) {
            $this->markTestSkipped('PHP Mess Detector is not available.');
        }


        $result = $codeMessDetector->run(self::getWhitelist(['php']));

        $output = "";
        if (file_exists($reportFile)) {
            $output = file_get_contents($reportFile);
        }

        $this->assertEquals(
            Command::EXIT_SUCCESS,
            $result,
            "PHP Code Mess has found error(s):" . PHP_EOL . $output
        );

        // delete empty reports
        if (file_exists($reportFile)) {
            unlink($reportFile);
        }
    }

    /**
     * Run copy paste detector on code
     *
     * @return void
     */
    public function testCopyPaste()
    {
        $reportFile = self::$reportDir . '/phpcpd_report.xml';
        $copyPasteDetector = new CopyPasteDetector($reportFile);

        if (!$copyPasteDetector->canRun()) {
            $this->markTestSkipped('PHP Copy/Paste Detector is not available.');
        }

        $blackList = [];
        foreach (glob(__DIR__ . '/_files/phpcpd/blacklist/*.txt') as $list) {
            $blackList = array_merge($blackList, file($list, FILE_IGNORE_NEW_LINES));
        }

        $copyPasteDetector->setBlackList($blackList);

        $result = $copyPasteDetector->run([BP]);

        $output = "";
        if (file_exists($reportFile)) {
            $output = file_get_contents($reportFile);
        }

        $this->assertTrue(
            $result,
            "PHP Copy/Paste Detector has found error(s):" . PHP_EOL . $output
        );
    }
}
