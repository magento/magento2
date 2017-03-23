<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Less;

use Magento\Framework\App\Utility;
use Magento\TestFramework\CodingStandard\Tool\CodeSniffer;
use Magento\TestFramework\CodingStandard\Tool\CodeSniffer\LessWrapper;
use PHPUnit_Framework_TestCase;
use Magento\Framework\App\Utility\Files;
use Magento\Test\Php\LiveCodeTest as PHPCodeTest;

/**
 * Set of tests for static code style
 */
class LiveCodeTest extends PHPUnit_Framework_TestCase
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
    public static function setUpBeforeClass()
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
        $reportFile = self::$reportDir . '/less_report.txt';
        $wrapper = new LessWrapper();
        $codeSniffer = new CodeSniffer(realpath(__DIR__ . '/_files/lesscs'), $reportFile, $wrapper);

        if (!$codeSniffer->canRun()) {
            $this->markTestSkipped('PHP Code Sniffer is not installed.');
        }

        $codeSniffer->setExtensions([LessWrapper::LESS_FILE_EXTENSION]);

        $fileList = PHPCodeTest::getWhitelist([LessWrapper::LESS_FILE_EXTENSION], __DIR__, __DIR__);

        $result = $codeSniffer->run($this->filterFiles($fileList));

        $this->assertEquals(
            0,
            $result,
            "PHP Code Sniffer has found {$result} error(s): See detailed report in {$reportFile}"
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
