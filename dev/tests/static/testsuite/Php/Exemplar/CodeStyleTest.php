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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class to test composed Magento coding standard against different code cases.
 * Used to ensure, that Magento coding standard rules (sniffs) really do what they are intended to do.
 *
 * @category    Magento
 * @package     Magento
 * @subpackage  static_tests
 */
class Php_Exemplar_CodeStyleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Inspection_CodeSniffer_Command
     */
    protected static $_cmd = null;

    public static function setUpBeforeClass()
    {
        $reportFile = __DIR__ . '/../../../tmp/phpcs_report.xml';
        self::$_cmd = new Inspection_CodeSniffer_Command(realpath(__DIR__ . '/../_files/phpcs'), $reportFile);
    }

    protected function setUp()
    {
        $reportFile = self::$_cmd->getReportFile();
        if (!is_dir(dirname($reportFile))) {
            mkdir(dirname($reportFile), 0777);
        }
    }

    protected function tearDown()
    {
        $reportFile = self::$_cmd->getReportFile();
        if (file_exists($reportFile)) {
            unlink($reportFile);
        }
        rmdir(dirname($reportFile));
    }

    public function testPhpCsAvailability()
    {
        $this->assertTrue(self::$_cmd->canRun(), 'PHP Code Sniffer command is not available.');
    }

    /**
     * @param string $inputFile
     * @param string $expectedResultFile
     * @dataProvider ruleDataProvider
     * @depends testPhpCsAvailability
     */
    public function testRule($inputFile, $expectedResultFile)
    {
        $expectedXml = simplexml_load_file($expectedResultFile);

        // rule is not implemented
        $elements = $expectedXml->xpath('/config/incomplete');
        if ($elements) {
            $message = (string)$elements[0];
            $this->markTestIncomplete("Rule for the fixture '{$inputFile}' is not implemented. {$message}");
        }

        // run additional methods before making test
        $elements = $expectedXml->xpath('/config/run');
        foreach ($elements as $element) {
            $method = (string)$element->attributes()->method;
            $this->$method();
        }

        self::$_cmd->run(array($inputFile));
        $resultXml = simplexml_load_file(self::$_cmd->getReportFile());
        $this->_assertTotalErrorsAndWarnings($resultXml, $expectedXml);
        $this->_assertErrors($resultXml, $expectedXml);
        $this->_assertWarnings($resultXml, $expectedXml);

        // verify that there has been at least one assertion performed
        if ($this->getCount() == 0) {
            $this->fail("Broken test: there has no assertions been performed for the fixture '{$inputFile}'.");
        }
    }

    /**
     * @return array
     */
    public function ruleDataProvider()
    {
        $inputDir = __DIR__ . '/_files/phpcs/input/';
        $expectationDir = __DIR__ . '/_files/phpcs/expected/';
        return $this->_getTestsAndExpectations($inputDir, $expectationDir);
    }

    /**
     * Recursively searches paths and adds files and expectations to the list of fixtures for tests
     *
     * @param string $inputDir
     * @param string $expectationDir
     * @return array
     */
    protected function _getTestsAndExpectations($inputDir, $expectationDir)
    {
        $result = array();
        $skipFiles = array('.', '..');
        $dir = dir($inputDir);
        do {
            $file = $dir->read();
            if (($file === false) || in_array($file, $skipFiles)) {
                continue;
            }

            $inputFilePath = $inputDir . $file;
            $expectationFilePath = $expectationDir . $file;

            if (is_dir($inputFilePath)) {
                $more = $this->_getTestsAndExpectations($inputFilePath . '/', $expectationFilePath . '/');
                $result = array_merge($result, $more);
                continue;
            }

            $pathinfo = pathinfo($inputFilePath);
            $expectationFilePath = $expectationDir . $pathinfo['filename'] . '.xml';
            $result[] = array($inputFilePath, $expectationFilePath);
        } while ($file !== false);
        $dir->close();

        return $result;
    }

    /**
     * Assert total expected quantity of errors and warnings
     *
     * @param SimpleXMLElement $report
     * @param SimpleXMLElement $expected
     */
    protected function _assertTotalErrorsAndWarnings($report, $expected)
    {
        $elements = $expected->xpath('/config/total') ?: array();
        if (!$elements) {
            return;
        }
        $numErrorsActual = count($report->xpath('/checkstyle/file/error[@severity="error"]'));
        $numWarningsActual = count($report->xpath('/checkstyle/file/error[@severity="warning"]'));

        $element = $elements[0];
        $attributes = $element->attributes();
        if (isset($attributes->errors)) {
            $numErrorsExpected = (string) $attributes->errors;
            $this->assertEquals(
                $numErrorsExpected,
                $numErrorsActual,
                'Expecting ' . $numErrorsExpected . ' errors, got ' . $numErrorsActual
            );
        }
        if (isset($attributes->warnings)) {
            $numWarningsExpected = (string) $attributes->warnings;
            $this->assertEquals(
                $numWarningsExpected,
                $numWarningsActual,
                'Expecting ' . $numWarningsExpected . ' warnings, got ' . $numWarningsActual
            );
        }
    }

    /**
     * Assert that errors correspond to expected errors
     *
     * @param SimpleXMLElement $report
     * @param SimpleXMLElement $expected
     */
    protected function _assertErrors($report, $expected)
    {
        $elements = $expected->xpath('/config/error') ?: array();
        foreach ($elements as $element) {
            $lineExpected = (string) $element->attributes()->line;
            $errorElement = $report->xpath('/checkstyle/file/error[@severity="error"][@line=' . $lineExpected . ']');
            $this->assertNotEmpty(
                $errorElement,
                'Expected error at line ' . $lineExpected . ' is not detected by PHPCS.'
            );
        }
    }

    /**
     * Assert that warnings correspond to expected warnings
     *
     * @param SimpleXMLElement $report
     * @param SimpleXMLElement $expected
     */
    protected function _assertWarnings($report, $expected)
    {
        $elements = $expected->xpath('/config/warning') ?: array();
        foreach ($elements as $element) {
            $lineExpected = (string) $element->attributes()->line;
            $errorElement = $report->xpath('/checkstyle/file/error[@severity="warning"][@line=' . $lineExpected . ']');
            $this->assertNotEmpty(
                $errorElement,
                'Expected warning at line ' . $lineExpected . ' is not detected by PHPCS.'
            );
        }
    }

    /**
     * Checks, whether short open tags are allowed.
     * Check-method, used by test-configs and executed before executing tests.
     *
     * @return null
     */
    protected function _checkShortTagsOn()
    {
        if (!ini_get('short_open_tag')) {
            $this->markTestSkipped('"short_open_tag" setting must be set to "On" to test this case.');
        }
    }

    /**
     * Checks, whether short open tags in ASP-style are allowed.
     * Check-method, used by test-configs and executed before executing tests.
     *
     * @return null
     */
    protected function _checkAspTagsOn()
    {
        if (!ini_get('asp_tags')) {
            $this->markTestSkipped('"asp tags" setting must be set to "On" to test this case.');
        }
    }
}
