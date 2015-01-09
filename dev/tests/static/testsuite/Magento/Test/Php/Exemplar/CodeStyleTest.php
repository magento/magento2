<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Class to test composed Magento coding standard against different code cases.
 * Used to ensure, that Magento coding standard rules (sniffs) really do what they are intended to do.
 *
 */
namespace Magento\Test\Php\Exemplar;

class CodeStyleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\CodingStandard\Tool\CodeSniffer
     */
    protected static $_cmd = null;

    private static $_reportFile = null;

    public static function setUpBeforeClass()
    {
        self::$_reportFile = __DIR__ . '/../../../tmp/phpcs_report.xml';
        $wrapper = new \Magento\TestFramework\CodingStandard\Tool\CodeSniffer\Wrapper();
        self::$_cmd = new \Magento\TestFramework\CodingStandard\Tool\CodeSniffer(
            realpath(__DIR__ . '/../_files/phpcs'),
            self::$_reportFile,
            $wrapper
        );
    }

    protected function setUp()
    {
        if (!is_dir(dirname(self::$_reportFile))) {
            mkdir(dirname(self::$_reportFile), 0777);
        }
    }

    protected function tearDown()
    {
        if (file_exists(self::$_reportFile)) {
            unlink(self::$_reportFile);
        }
        rmdir(dirname(self::$_reportFile));
    }

    public function testPhpCsAvailability()
    {
        $this->assertTrue(self::$_cmd->canRun(), 'PHP Code Sniffer command is not available.');
    }

    /**
     * @depends testPhpCsAvailability
     */
    public function testRule()
    {
        $invoker = new \Magento\Framework\Test\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $inputFile
             * @param string $expectedResultFile
             */
            function ($inputFile, $expectedResultFile) {
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
                    $this->{$method}();
                }

                self::$_cmd->run([$inputFile]);
                $resultXml = simplexml_load_file(self::$_reportFile);
                $this->_assertTotalErrorsAndWarnings($resultXml, $expectedXml);
                $this->_assertErrors($resultXml, $expectedXml);
                $this->_assertWarnings($resultXml, $expectedXml);

                // verify that there has been at least one assertion performed
                if ($this->getCount() == 0) {
                    $this->fail("Broken test: there has no assertions been performed for the fixture '{$inputFile}'.");
                }
            },
            $this->ruleDataProvider()
        );
    }

    /**
     * @return array
     */
    public function ruleDataProvider()
    {
        $inputDir = __DIR__ . '/CodeStyleTest/phpcs/input/';
        $expectationDir = __DIR__ . '/CodeStyleTest/phpcs/expected/';
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
        $result = [];
        $skipFiles = ['.', '..'];
        $dir = dir($inputDir);
        do {
            $file = $dir->read();
            if ($file === false || in_array($file, $skipFiles)) {
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
            $result[] = [$inputFilePath, $expectationFilePath];
        } while ($file !== false);
        $dir->close();

        return $result;
    }

    /**
     * Assert total expected quantity of errors and warnings
     *
     * @param \SimpleXMLElement $report
     * @param \SimpleXMLElement $expected
     */
    protected function _assertTotalErrorsAndWarnings($report, $expected)
    {
        $elements = $expected->xpath('/config/total') ?: [];
        if (!$elements) {
            return;
        }

        list($numErrorsActual, $numWarningsActual) = $this->_calculateCountErrors($report);

        $element = $elements[0];
        $attributes = $element->attributes();
        if (isset($attributes->errors)) {
            $numErrorsExpected = (string)$attributes->errors;
            $this->assertEquals(
                $numErrorsExpected,
                $numErrorsActual,
                'Expecting ' . $numErrorsExpected . ' errors, got ' . $numErrorsActual
            );
        }
        if (isset($attributes->warnings)) {
            $numWarningsExpected = (string)$attributes->warnings;
            $this->assertEquals(
                $numWarningsExpected,
                $numWarningsActual,
                'Expecting ' . $numWarningsExpected . ' warnings, got ' . $numWarningsActual
            );
        }
    }

    /**
     * Calculate count errors and warnings
     *
     * @param \SimpleXMLElement $report
     * @return array
     */
    protected function _calculateCountErrors($report)
    {
        $errorNode = $report->xpath('/checkstyle/file/error[@severity="error"]') ?: [];
        $warningNode = $report->xpath('/checkstyle/file/error[@severity="warning"]') ?: [];
        $numErrorsActual = count($errorNode);
        $numWarningsActual = count($warningNode);
        return [$numErrorsActual, $numWarningsActual];
    }

    /**
     * Assert that errors correspond to expected errors
     *
     * @param \SimpleXMLElement $report
     * @param \SimpleXMLElement $expected
     */
    protected function _assertErrors($report, $expected)
    {
        $elements = $expected->xpath('/config/error') ?: [];
        foreach ($elements as $element) {
            $lineExpected = (string)$element->attributes()->line;
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
     * @param \SimpleXMLElement $report
     * @param \SimpleXMLElement $expected
     */
    protected function _assertWarnings($report, $expected)
    {
        $elements = $expected->xpath('/config/warning') ?: [];
        foreach ($elements as $element) {
            $lineExpected = (string)$element->attributes()->line;
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
