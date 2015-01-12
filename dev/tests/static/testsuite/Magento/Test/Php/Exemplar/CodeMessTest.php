<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Self-assessment for PHP Mess Detector tool and its configuration (rule set)
 */
namespace Magento\Test\Php\Exemplar;

class CodeMessTest extends \PHPUnit_Framework_TestCase
{
    const PHPMD_REQUIRED_VERSION = '1.1.0';

    /**
     * @var \Magento\TestFramework\CodingStandard\Tool\CodeMessDetector
     */
    protected static $_messDetector = null;

    /**
     * Ruleset file
     *
     * @var string|null
     */
    protected static $_rulesetFile = null;

    /**
     * Report file
     *
     * @var string|null
     */
    protected static $_reportFile = null;

    public static function setUpBeforeClass()
    {
        self::$_rulesetFile = realpath(__DIR__ . '/../_files/phpmd/ruleset.xml');
        self::$_reportFile = __DIR__ . '/../../../tmp/phpmd_report.xml';
        self::$_messDetector = new \Magento\TestFramework\CodingStandard\Tool\CodeMessDetector(
            self::$_rulesetFile,
            self::$_reportFile
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

    public function testRulesetFormat()
    {
        $this->assertFileExists(self::$_rulesetFile);
        $doc = new \DOMDocument();
        $doc->load(self::$_rulesetFile);

        libxml_use_internal_errors(true);
        $isValid = $doc->schemaValidate(__DIR__ . '/_files/phpmd_ruleset.xsd');
        $errors = "XML-file is invalid.\n";
        if ($isValid === false) {
            foreach (libxml_get_errors() as $error) {
                /* @var libXMLError $error */
                $errors .= "{$error->message} File: {$error->file} Line: {$error->line}\n";
            }
        }
        libxml_use_internal_errors(false);
        $this->assertTrue($isValid, $errors);
    }

    public function testPhpMdAvailability()
    {
        $this->assertTrue(self::$_messDetector->canRun(), 'PHP Mess Detector command is not available.');
    }

    /**
     * @depends testRulesetFormat
     * @depends testPhpMdAvailability
     */
    public function testRuleViolation()
    {
        $invoker = new \Magento\Framework\Test\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $inputFile
             * @param string|array $expectedXpaths
             */
            function ($inputFile, $expectedXpaths) {
                $this->assertNotEquals(
                    \PHP_PMD_TextUI_Command::EXIT_SUCCESS,
                    self::$_messDetector->run([$inputFile]),
                    "PHP Mess Detector has failed to identify problem at the erroneous file {$inputFile}"
                );

                $actualReportXml = simplexml_load_file(self::$_reportFile);
                $expectedXpaths = (array)$expectedXpaths;
                foreach ($expectedXpaths as $expectedXpath) {
                    $this->assertNotEmpty(
                        $actualReportXml->xpath($expectedXpath),
                        "Expected xpath: '{$expectedXpath}' for file: '{$inputFile}'"
                    );
                }
            },
            include __DIR__ . '/_files/phpmd/data.php'
        );
    }
}
