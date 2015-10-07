<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
        self::$pathToSource = Utility\Files::init()->getPathToSource();
        self::$reportDir = self::$pathToSource . '/dev/tests/static/report';
        if (!is_dir(self::$reportDir)) {
            mkdir(self::$reportDir, 0770);
        }
    }

    /**
     * Returns whitelist based on blacklist and git changed files
     *
     * @param array $fileTypes
     * @return array
     */
    public static function getWhitelist($fileTypes = ['php'])
    {
        $directoriesToCheck = Files::init()->readLists(__DIR__ . '/_files/whitelist/common.txt');

        $changedFiles = [];
        foreach (glob(__DIR__ . '/_files/changed_files*') as $listFile) {
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
        $reportFile = self::$reportDir . '/phpcs_psr2_report.xml';
        $wrapper = new Wrapper();
        $codeSniffer = new CodeSniffer('PSR2', $reportFile, $wrapper);
        if (!$codeSniffer->canRun()) {
            $this->markTestSkipped('PHP Code Sniffer is not installed.');
        }
        if (version_compare($wrapper->version(), '1.4.7') === -1) {
            $this->markTestSkipped('PHP Code Sniffer Build Too Old.');
        }

        $result = $codeSniffer->run(self::getWhitelist());

        $this->assertEquals(
            0,
            $result,
            "PHP Code Sniffer has found {$result} error(s): See detailed report in {$reportFile}"
        );
    }

    /**
     * Run the magento specific coding standards on the code
     *
     * @return void
     */
    public function testCodeStyle()
    {
        $reportFile = self::$reportDir . '/phpcs_report.xml';
        $wrapper = new Wrapper();
        $codeSniffer = new CodeSniffer(realpath(__DIR__ . '/_files/phpcs'), $reportFile, $wrapper);
        if (!$codeSniffer->canRun()) {
            $this->markTestSkipped('PHP Code Sniffer is not installed.');
        }
        $codeSniffer->setExtensions(['php', 'phtml']);
        $result = $codeSniffer->run(self::getWhitelist(['php', 'phtml']));
        $this->assertEquals(
            0,
            $result,
            "PHP Code Sniffer has found {$result} error(s): See detailed report in {$reportFile}"
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
        $reportFile = self::$reportDir . '/phpcs_annotations_report.xml';
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
        $this->assertEquals(
            0,
            $result,
            "PHP Code Sniffer has found {$result} error(s): See detailed report in {$reportFile}"
        );
    }

    /**
     * Run mess detector on code
     *
     * @return void
     */
    public function testCodeMess()
    {
        $reportFile = self::$reportDir . '/phpmd_report.xml';
        $codeMessDetector = new CodeMessDetector(realpath(__DIR__ . '/_files/phpmd/ruleset.xml'), $reportFile);

        if (!$codeMessDetector->canRun()) {
            $this->markTestSkipped('PHP Mess Detector is not available.');
        }

        $this->assertEquals(
            Command::EXIT_SUCCESS,
            $codeMessDetector->run(self::getWhitelist(['php'])),
            "PHP Code Mess has found error(s): See detailed report in {$reportFile}"
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

        $this->assertTrue(
            $copyPasteDetector->run([BP]),
            "PHP Copy/Paste Detector has found error(s): See detailed report in {$reportFile}"
        );
    }

    public function testDeadCode()
    {
        if (!class_exists('SebastianBergmann\PHPDCD\Analyser')) {
            $this->markTestSkipped('PHP Dead Code Detector is not available.');
        }
        $analyser = new \SebastianBergmann\PHPDCD\Analyser();
        $declared = [];
        $called = [];
        $collectedFiles = Files::init()->getPhpFiles(
            Files::INCLUDE_APP_CODE
            | Files::INCLUDE_PUB_CODE
            | Files::INCLUDE_LIBS
            | Files::INCLUDE_TEMPLATES
            | Files::INCLUDE_TESTS
            | Files::AS_DATA_SET
            | Files::INCLUDE_NON_CLASSES
        );
        foreach ($collectedFiles as $file) {
            $file = array_pop($file);
            $analyser->analyseFile($file);
            foreach ($analyser->getFunctionDeclarations() as $function => $declaration) {
                $declaration = $declaration; //avoid "unused local variable" error and non-effective array_keys call
                if (strpos($function, '::') === false) {
                    $method = $function;
                } else {
                    list($class, $method) = explode('::', $function);
                }
                $declared[$method] = $function;
            }
            foreach ($analyser->getFunctionCalls() as $function => $usages) {
                $usages = $usages; //avoid "unused local variable" error and non-effective array_keys call
                if (strpos($function, '::') === false) {
                    $method = $function;
                } else {
                    list($class, $method) = explode('::', $function);
                }
                $called[$method] = 1;
            }
        }

        foreach ($called as $method => $value) {
            $value = $value; //avoid "unused local variable" error and non-effective array_keys call
            unset($declared[$method]);
        }
        $declared = $this->filterUsedObserverMethods($declared);
        $declared = $this->filterUsedPersistentObserverMethods($declared);
        $declared = $this->filterUsedCrontabObserverMethods($declared);
        if ($declared) {
            $this->fail('Dead code detected:' . PHP_EOL . implode(PHP_EOL, $declared));
        }
    }

    /**
     * @param string[] $methods
     * @return string[]
     * @throws \Exception
     */
    private function filterUsedObserverMethods($methods)
    {
        foreach (Files::init()->getConfigFiles('{*/events.xml,events.xml}') as $file) {
            $file = array_pop($file);

            $doc = new \DOMDocument();
            $doc->load($file);
            foreach ($doc->getElementsByTagName('observer') as $observer) {
                /** @var \DOMElement $observer */
                $method = $observer->getAttribute('method');
                unset($methods[$method]);
            }
        }
        return $methods;
    }

    /**
     * @param string[] $methods
     * @return string[]
     * @throws \Exception
     */
    private function filterUsedPersistentObserverMethods($methods)
    {
        foreach (Files::init()->getConfigFiles('{*/persistent.xml,persistent.xml}') as $file) {
            $file = array_pop($file);

            $doc = new \DOMDocument();
            $doc->load($file);
            foreach ($doc->getElementsByTagName('method') as $method) {
                /** @var \DOMElement $method */
                unset($methods[$method->textContent]);
            }
        }
        return $methods;
    }

    /**
     * @param string[] $methods
     * @return string[]
     * @throws \Exception
     */
    private function filterUsedCrontabObserverMethods($methods)
    {
        foreach (Files::init()->getConfigFiles('{*/crontab.xml,crontab.xml}') as $file) {
            $file = array_pop($file);

            $doc = new \DOMDocument();
            $doc->load($file);
            foreach ($doc->getElementsByTagName('job') as $job) {
                /** @var \DOMElement $job */
                unset($methods[$job->getAttribute('method')]);
            }
        }
        return $methods;
    }
}
