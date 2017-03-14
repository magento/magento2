<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Error;

require_once __DIR__ . '/../../../../../../../pub/errors/processor.php';

class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /** @var Processor */
    private $processor;

    public function setUp()
    {
        $this->processor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(Processor::class);
    }

    public function tearDown()
    {
        unlink($this->processor->_reportDir . '/' . $this->processor->reportId);
    }

    public function testSaveAndLoadReport()
    {
        $reportData = [
            0 => 'exceptionMessage',
            1 => 'exceptionTrace',
            'script_name' => 'processor.php'
        ];
        $expectedReportData = array_merge($reportData, ['url' => '']);
        $this->processor->saveReport($reportData);
        $this->assertFileExists($this->processor->_reportDir . '/' . $this->processor->reportId);
        $this->assertEquals($expectedReportData, $this->processor->reportData);

        // Store report id and reset our objects
        $reportId = $this->processor->reportId;
        $this->setUp();
        $this->assertEmpty($this->processor->reportData);

        // Reload report from file and verify contents still match
        $this->processor->loadReport($reportId);
        $this->assertEquals($expectedReportData, $this->processor->reportData);
    }
}
