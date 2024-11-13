<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Less;

use Magento\TestFramework\CodingStandard\Tool\CodeSniffer;
use Magento\Framework\App\Utility\Files;
use Magento\Test\Php\LiveCodeTest as PHPCodeTest;
use Magento\TestFramework\CodingStandard\Tool\CodeSniffer\Wrapper;
use PHPUnit\Framework\TestCase;

/**
 * Set of tests for static code style
 */
class LiveCodeTest extends TestCase
{
    private const FILE_EXTENSION = 'less';
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
    public function testCodeStyle()
    {
        $reportFile = self::$reportDir . '/csless_report.txt';
        $codeSniffer = new CodeSniffer('Magento', $reportFile, new Wrapper());
        $codeSniffer->setExtensions([self::FILE_EXTENSION]);
        $fileList = PHPCodeTest::getWhitelist([self::FILE_EXTENSION], __DIR__, __DIR__);
        $result = $codeSniffer->run($this->filterFiles($fileList));
        $report = file_exists($reportFile) ? file_get_contents($reportFile) : '';
        $this->assertEquals(
            0,
            $result,
            "PHP Code Sniffer detected {$result} violation(s): " . PHP_EOL . $report
        );
    }

    /**
     * Skip blacklisted files
     *
     * @param array $fileList
     * @return array
     * @throws \Exception
     */
    private function filterFiles(array $fileList)
    {
        $blackListFiles = Files::init()->readLists(__DIR__ . '/_files/blacklist/*.txt');

        $filter = function ($value) use ($blackListFiles) {
            return !in_array($value, $blackListFiles);
        };

        return array_filter($fileList, $filter);
    }
}
