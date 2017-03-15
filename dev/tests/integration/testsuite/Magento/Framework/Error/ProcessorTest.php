<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Error;

require_once __DIR__ . '/../../../../../../../pub/errors/processor.php';

class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testSaveAndLoadReport()
    {
        $reportData = [
            0 => 'exceptionMessage',
            1 => 'exceptionTrace',
            'script_name' => 'processor.php'
        ];
        $expectedReportData = array_merge($reportData, ['url' => '']);
        $saveProcessor = $this->createProcessor();
        $saveProcessor->saveReport($reportData);
        if (!$saveProcessor->reportId) {
            $this->fail("Failed to generate report id");
        }
        $this->assertFileExists($saveProcessor->_reportDir . '/' . $saveProcessor->reportId);
        $this->assertEquals($expectedReportData, $saveProcessor->reportData);

        $loadProcessor = $this->createProcessor();
        $loadProcessor->loadReport($saveProcessor->reportId);
        $this->assertEquals($expectedReportData, $loadProcessor->reportData, "File contents of report don't match");

        unlink($saveProcessor->_reportDir . '/' . $saveProcessor->reportId);
    }

    public function testLoadReportException()
    {
        $this->setExpectedException(\Exception::class, "Report not found");
        $loadProcessor = $this->createProcessor();
        $loadProcessor->loadReport(1);
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
