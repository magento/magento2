<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Error;

require_once __DIR__ . '/../../../../../../../pub/errors/processor.php';

class ProcessorTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Error\Processor */
    private $processor;

    public function setUp()
    {
        $this->processor = $this->createProcessor();
    }

    public function tearDown()
    {
        if ($this->processor->reportId) {
            unlink($this->processor->_reportDir . '/' . $this->processor->reportId);
        }
    }

    public function testSaveAndLoadReport()
    {
        $reportData = [
            0 => 'exceptionMessage',
            1 => 'exceptionTrace',
            'script_name' => 'processor.php'
        ];
        $expectedReportData = array_merge($reportData, ['url' => '']);
        $this->processor = $this->createProcessor();
        $this->processor->saveReport($reportData);
        if (!$this->processor->reportId) {
            $this->fail("Failed to generate report id");
        }
        $this->assertFileExists($this->processor->_reportDir . '/' . $this->processor->reportId);
        $this->assertEquals($expectedReportData, $this->processor->reportData);

        $loadProcessor = $this->createProcessor();
        $loadProcessor->loadReport($this->processor->reportId);
        $this->assertEquals($expectedReportData, $loadProcessor->reportData, "File contents of report don't match");
    }

    /**
     * @return \Magento\Framework\Error\Processor
     */
    private function createProcessor()
    {
        return \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Error\Processor::class);
    }
}
