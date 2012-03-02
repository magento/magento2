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
 * Self-assessment for PHP Mess Detector tool and its configuration (rule set)
 */
class Php_Exemplar_CodeMessTest extends PHPUnit_Framework_TestCase
{
    const PHPMD_REQUIRED_VERSION = '1.1.0';

    /**
     * @var Inspection_MessDetector_Command
     */
    protected static $_cmd = null;

    public static function setUpBeforeClass()
    {
        $rulesetFile = realpath(__DIR__ . '/../_files/phpmd/ruleset.xml');
        $reportFile = __DIR__ . '/../../../tmp/phpmd_report.xml';
        self::$_cmd = new Inspection_MessDetector_Command($rulesetFile, $reportFile);
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

    public function testRulesetFormat()
    {
        $rulesetFile = self::$_cmd->getRulesetFile();
        $this->assertFileExists($rulesetFile);
        $doc = new DOMDocument();
        $doc->load($rulesetFile);

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
        $this->assertTrue(self::$_cmd->canRun(), 'PHP Mess Detector command is not available.');
        $minVersion = self::PHPMD_REQUIRED_VERSION;
        $version = self::$_cmd->getVersion();
        $this->assertTrue(version_compare($version, $minVersion, '>='),
            "PHP Mess Detector minimal required version is '{$minVersion}'. The current version is '{$version}'."
        );
    }

    /**
     * @param string $inputFile
     * @param string|array $expectedXpaths
     * @depends testRulesetFormat
     * @depends testPhpMdAvailability
     * @dataProvider ruleViolationDataProvider
     */
    public function testRuleViolation($inputFile, $expectedXpaths)
    {
        $this->assertFalse(self::$_cmd->run(
            array($inputFile)), "PHP Mess Detector has failed to identify problem at the erroneous file {$inputFile}"
        );

        $actualReportXml = simplexml_load_file(self::$_cmd->getReportFile());
        $expectedXpaths = (array)$expectedXpaths;
        foreach ($expectedXpaths as $expectedXpath) {
            $this->assertNotEmpty(
                $actualReportXml->xpath($expectedXpath),
                "Expected xpath: '$expectedXpath' for file: '$inputFile'"
            );
        }
    }

    /**
     * @return array
     */
    public function ruleViolationDataProvider()
    {
        return include(__DIR__ . '/_files/phpmd/data.php');
    }
}
