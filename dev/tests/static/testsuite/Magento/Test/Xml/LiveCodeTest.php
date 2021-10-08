<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Xml;

use Magento\Framework\App\Utility\Files;
use Magento\TestFramework\CodingStandard\Tool\CodeSniffer;
use Magento\Test\Php\LiveCodeTest as PHPCodeTest;
use Magento\TestFramework\CodingStandard\Tool\CodeSniffer\Wrapper;
use Magento\TestFramework\Utility\FilesSearch;
use PHPUnit\Framework\TestCase;

/**
 * Set of tests for static code style
 */
class LiveCodeTest extends TestCase
{
    private const FILE_EXTENSION = 'xml';

    /**
     * @var string
     */
    private static $reportDir = '';

    /**
     * Setup basics for all tests
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        self::$reportDir = BP . '/dev/tests/static/report';
        if (!is_dir(self::$reportDir)) {
            mkdir(self::$reportDir, 0770);
        }
    }

    /**
     * Run the magento specific coding standards on the codedata/UpgradeData.php
     *
     * @return void
     */
    public function testCodeStyle(): void
    {
        $reportFile = self::$reportDir . '/xml_report.txt';
        if (!file_exists($reportFile)) {
            touch($reportFile);
        }
        $codeSniffer = new CodeSniffer('Magento', $reportFile, new Wrapper());
        $codeSniffer->setExtensions([self::FILE_EXTENSION]);
        $fileList =  $this->isFullScan() ? array_column(Files::init()->getXmlFiles(), '0')
            : PHPCodeTest::getWhitelist([self::FILE_EXTENSION]);
        $result = $codeSniffer->run($fileList);
        $report = file_exists($reportFile) ? file_get_contents($reportFile) : '';
        $this->assertEquals(
            0,
            $result,
            "PHP Code Sniffer detected {$result} violation(s): " . PHP_EOL . $report
        );
    }

    /**
     * Returns whether a full scan was requested.
     *
     * This can be set in the `phpunit.xml` used to run these test cases, by setting the constant
     * `TESTCODESTYLE_IS_FULL_SCAN` to `1`, e.g.:
     * ```xml
     * <php>
     *     <!-- TESTCODESTYLE_IS_FULL_SCAN - specify if full scan should be performed for test code style test -->
     *     <const name="TESTCODESTYLE_IS_FULL_SCAN" value="0"/>
     * </php>
     * ```
     *
     * @return bool
     */
    private function isFullScan(): bool
    {
        return defined('TESTCODESTYLE_IS_FULL_SCAN') && TESTCODESTYLE_IS_FULL_SCAN === '1';
    }
}
