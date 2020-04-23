<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Error;

use Magento\TestFramework\Helper\Bootstrap;

require_once __DIR__ . '/../../../../../../../pub/errors/processor.php';

class ProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->processor = $this->createProcessor();
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    protected function tearDown()
    {
        $reportDir = $this->processor->_reportDir;
        $this->removeDirRecursively($reportDir);
    }

    /**
     * @param int $logReportDirNestingLevel
     * @param int $logReportDirNestingLevelChanged
     * @param string $exceptionMessage
     * @dataProvider dataProviderSaveAndLoadReport
     */
    public function testSaveAndLoadReport(
        int $logReportDirNestingLevel,
        int $logReportDirNestingLevelChanged,
        string $exceptionMessage
    ) {
        $_ENV['MAGE_ERROR_REPORT_DIR_NESTING_LEVEL'] = $logReportDirNestingLevel;
        $reportData = [
            0 => $exceptionMessage,
            1 => 'exceptionTrace',
            'script_name' => 'processor.php'
        ];
        $reportData['report_id'] = hash('sha256', implode('', $reportData));
        $expectedReportData = array_merge($reportData, ['url' => '']);
        $processor = $this->createProcessor();
        $processor->saveReport($reportData);
        $reportId = $processor->reportId;
        if (!$reportId) {
            $this->fail("Failed to generate report id");
        }
        $this->assertEquals($expectedReportData, $processor->reportData);
        $_ENV['MAGE_ERROR_REPORT_DIR_NESTING_LEVEL'] = $logReportDirNestingLevelChanged;
        $processor = $this->createProcessor();
        $processor->loadReport($reportId);
        $this->assertEquals($expectedReportData, $processor->reportData, "File contents of report don't match");
    }

    /**
     * Data Provider for testSaveAndLoadReport
     *
     * @return array
     */
    public function dataProviderSaveAndLoadReport(): array
    {
        return [
            [
                'logReportDirNestingLevel' => 0,
                'logReportDirNestingLevelChanged' => 0,
                'exceptionMessage' => '$exceptionMessage 0',
            ],
            [
                'logReportDirNestingLevel' => 1,
                'logReportDirNestingLevelChanged' => 1,
                'exceptionMessage' => '$exceptionMessage 1',
            ],
            [
                'logReportDirNestingLevel' => 2,
                'logReportDirNestingLevelChanged' => 2,
                'exceptionMessage' => '$exceptionMessage 2',
            ],
            [
                'logReportDirNestingLevel' => 3,
                'logReportDirNestingLevelChanged' => 23,
                'exceptionMessage' => '$exceptionMessage 2',
            ],
            [
                'logReportDirNestingLevel' => 32,
                'logReportDirNestingLevelChanged' => 32,
                'exceptionMessage' => '$exceptionMessage 3',
            ],
            [
                'logReportDirNestingLevel' => 100,
                'logReportDirNestingLevelChanged' => 100,
                'exceptionMessage' => '$exceptionMessage 100',
            ],
        ];
    }

    /**
     * @return Processor
     */
    private function createProcessor(): Processor
    {
        return Bootstrap::getObjectManager()->create(Processor::class);
    }

    /**
     * Remove dir recursively
     *
     * @param string $dir
     * @param int $i
     * @return bool
     * @throws \Exception
     */
    private function removeDirRecursively(string $dir, int $i = 0): bool
    {
        if ($i >= 100) {
            throw new \Exception('Emergency exit from recursion');
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $i++;
            (is_dir("$dir/$file"))
                ? $this->removeDirRecursively("$dir/$file", $i)
                : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}
