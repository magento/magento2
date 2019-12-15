<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Html;

use Magento\Framework\App\Utility;
use Magento\TestFramework\CodingStandard\Tool\CodeSniffer;
use Magento\Framework\App\Utility\Files;
use Magento\Test\Php\LiveCodeTest as PHPCodeTest;
use PHPUnit\Framework\TestCase;

/**
 * Set of tests for static code style
 */
class LiveCodeTest extends TestCase
{
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
     * Run the magento specific coding standards on the code
     *
     * @return void
     */
    public function testCodeStyle(): void
    {
        $reportFile = self::$reportDir . '/html_report.txt';
        $wrapper = new CodeSniffer\HtmlWrapper();
        $codeSniffer = new CodeSniffer(realpath(__DIR__ . '/_files/html'), $reportFile, $wrapper);
        if (!$codeSniffer->canRun()) {
            $this->markTestSkipped('PHP Code Sniffer is not installed.');
        }
        $codeSniffer->setExtensions([CodeSniffer\HtmlWrapper::FILE_EXTENSION]);
        //Looking for changed .html files
        $fileList = PHPCodeTest::getWhitelist([CodeSniffer\HtmlWrapper::FILE_EXTENSION], __DIR__, __DIR__);

        $result = $codeSniffer->run($fileList);

        $report = file_exists($reportFile) ? file_get_contents($reportFile) : "";
        $this->assertEquals(
            0,
            $result,
            "PHP Code Sniffer has found {$result} error(s): " . PHP_EOL . $report
        );
    }
}
