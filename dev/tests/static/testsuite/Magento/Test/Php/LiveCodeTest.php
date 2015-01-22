<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Php;

use Magento\Framework\Test\Utility;
use Magento\TestFramework\CodingStandard\Tool\CodeMessDetector;
use Magento\TestFramework\CodingStandard\Tool\CodeSniffer;
use Magento\TestFramework\CodingStandard\Tool\CodeSniffer\Wrapper;
use Magento\TestFramework\CodingStandard\Tool\CopyPasteDetector;
use PHP_PMD_TextUI_Command;
use PHPUnit_Framework_TestCase;
use Magento\Framework\Test\Utility\Files;

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
     * @var array
     */
    protected static $whiteList = [];

    /**
     * @var array
     */
    protected static $blackList = [];

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
            mkdir(self::$reportDir, 0777);
        }
        self::setupFileLists();
    }

    /**
     * Helper method to setup the black and white lists
     *
     * @param string $type
     * @return void
     */
    public static function setupFileLists($type = '')
    {
        if ($type != '' && !preg_match('/\/$/', $type)) {
            $type = $type . '/';
        }
        self::$whiteList = Utility\Files::readLists(__DIR__ . '/_files/' . $type . 'whitelist/*.txt');
        self::$blackList = Utility\Files::readLists(__DIR__ . '/_files/' . $type . 'blacklist/*.txt');
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
        if (version_compare($codeSniffer->version(), '1.4.7') === -1) {
            $this->markTestSkipped('PHP Code Sniffer Build Too Old.');
        }
        self::setupFileLists('phpcs');
        $result = $codeSniffer->run(self::$whiteList, self::$blackList, ['php']);
        $this->assertFileExists(
            $reportFile,
            'Expected ' . $reportFile . ' to be created by phpcs run with PSR2 standard'
        );
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
        self::setupFileLists();
        $result = $codeSniffer->run(self::$whiteList, self::$blackList, ['php', 'phtml']);
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
        self::setupFileLists('phpcs');

        $severity = 0; // Change to 5 to see the warnings
        $this->assertEquals(
            0,
            $result = $codeSniffer->run(self::$whiteList, self::$blackList, ['php'], $severity),
            "PHP Code Sniffer has found {$result} error(s): See detailed report in {$reportFile}"
        );
    }

    /**
     * Run mess detector on code
     *
     * @param array $whiteList
     * @return void
     * @dataProvider whiteListDataProvider
     */
    public function testCodeMess($whiteList)
    {
        if (count($whiteList) == 1) {
            $formattedPath = preg_replace('~/~', '_', preg_replace('~' . self::$pathToSource . '~', '', $whiteList[0]));
        } else {
            $formattedPath = '_app_lib';
        }
        $reportFile = self::$reportDir . '/phpmd_report' . $formattedPath . '.xml';
        $codeMessDetector = new CodeMessDetector(realpath(__DIR__ . '/_files/phpmd/ruleset.xml'), $reportFile);

        if (!$codeMessDetector->canRun()) {
            $this->markTestSkipped('PHP Mess Detector is not available.');
        }

        $this->assertEquals(
            PHP_PMD_TextUI_Command::EXIT_SUCCESS,
            $codeMessDetector->run($whiteList, self::$blackList),
            "PHP Code Mess has found error(s): See detailed report in {$reportFile}"
        );

        // delete empty reports
        unlink($reportFile);
    }

    /**
     * To improve the test execution performance the whitelist is split into smaller parts:
     *  - in case of dev code (tests, tools, etc) each whitelist entry is fed separately to phpmd
     *  - app/lib code is still being executed within a single whitelist to make sure that all design
     *    metrics (depth of inheritance, number of children, etc.) are being calculated in a correct way.
     * @return array
     */
    public function whiteListDataProvider()
    {
        $whiteList = [];
        $testCodePattern = '~' . self::$pathToSource . '/dev/~';
        $nonTestCode = [];

        self::setupFileLists();

        foreach (self::$whiteList as $path) {
            if (!preg_match($testCodePattern, $path)) {
                $nonTestCode[] = $path;
            } else {
                $whiteList[] = [[$path]];
            }
        }
        $whiteList[] = [$nonTestCode];

        return $whiteList;
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

        self::setupFileLists();
        $blackList = [];
        foreach (glob(__DIR__ . '/_files/phpcpd/blacklist/*.txt') as $list) {
            $blackList = array_merge($blackList, file($list, FILE_IGNORE_NEW_LINES));
        }

        $this->assertTrue(
            $copyPasteDetector->run([], $blackList),
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
        foreach (Files::init()->getPhpFiles() as $file) {
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
