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
namespace Magento\Test\Php;

use Magento\TestFramework\CodingStandard\Tool\CodeMessDetector;
use Magento\TestFramework\CodingStandard\Tool\CodeSniffer\Wrapper;
use Magento\TestFramework\CodingStandard\Tool\CodeSniffer;
use Magento\TestFramework\CodingStandard\Tool\CopyPasteDetector;
use Magento\TestFramework\Utility;
use PHP_PMD_TextUI_Command;
use PHPUnit_Framework_TestCase;

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
    protected static $whiteList = array();

    /**
     * @var array
     */
    protected static $blackList = array();

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
        $result = $codeSniffer->run(self::$whiteList, self::$blackList, array('php'));
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
        $result = $codeSniffer->run(self::$whiteList, self::$blackList, array('php', 'phtml'));
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
            $result = $codeSniffer->run(self::$whiteList, self::$blackList, array('php'), $severity),
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
        $whiteList = array();
        $testCodePattern = '~' . self::$pathToSource . '/dev/~';
        $nonTestCode = array();

        self::setupFileLists();

        foreach (self::$whiteList as $path) {
            if (!preg_match($testCodePattern, $path)) {
                $nonTestCode[] = $path;
            } else {
                $whiteList[] = array(array($path));
            }
        }
        $whiteList[] = array($nonTestCode);

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
        $blackList = array();
        foreach (glob(__DIR__ . '/_files/phpcpd/blacklist/*.txt') as $list) {
            $blackList = array_merge($blackList, file($list, FILE_IGNORE_NEW_LINES));
        }

        $this->assertTrue(
            $copyPasteDetector->run(array(), $blackList),
            "PHP Copy/Paste Detector has found error(s): See detailed report in {$reportFile}"
        );
    }
}
