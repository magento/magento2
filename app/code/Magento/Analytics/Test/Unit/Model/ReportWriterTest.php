<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\ConfigInterface;
use Magento\Analytics\Model\ProviderFactory;
use Magento\Analytics\Model\ReportWriter;
use Magento\Analytics\ReportXml\DB\ReportValidator;
use Magento\Analytics\ReportXml\ReportProvider;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ReportWriterTest
 */
class ReportWriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configInterfaceMock;

    /**
     * @var ReportValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reportValidatorMock;

    /**
     * @var ProviderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $providerFactoryMock;

    /**
     * @var ReportProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reportProviderMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryMock;

    /**
     * @var ReportWriter
     */
    private $reportWriter;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->configInterfaceMock = $this->getMockBuilder(ConfigInterface::class)->getMockForAbstractClass();
        $this->reportValidatorMock = $this->getMockBuilder(ReportValidator::class)
            ->disableOriginalConstructor()->getMock();
        $this->providerFactoryMock = $this->getMockBuilder(ProviderFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->reportProviderMock = $this->getMockBuilder(ReportProvider::class)
            ->disableOriginalConstructor()->getMock();
        $this->directoryMock = $this->getMockBuilder(WriteInterface::class)->getMockForAbstractClass();
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->reportWriter = $this->objectManagerHelper->getObject(
            ReportWriter::class,
            [
                'config' => $this->configInterfaceMock,
                'reportValidator' => $this->reportValidatorMock,
                'providerFactory' => $this->providerFactoryMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testWrite()
    {
        $reportName = 'testProvider';
        $providerClass = 'Magento\Analytics\Provider';
        $errors = [];
        $fileData = [
            [1, 'Shoes Usual']
        ];
        $this->configInterfaceMock->expects($this->once())->method('get')->willReturn([
            [
                'providers' => [
                    [
                        [
                            'name' => $reportName,
                            'class' => $providerClass
                        ]
                    ]
                ]
            ]
        ]);
        $this->providerFactoryMock->expects($this->once())->method('create')
            ->willReturn($this->reportProviderMock);
        $this->reportProviderMock->expects($this->once())->method('getReport')
            ->with($reportName)
            ->willReturn($fileData);
        $errorStreamMock = $this->getMockBuilder(
            \Magento\Framework\Filesystem\File\WriteInterface::class
        )->getMockForAbstractClass();
        $errorStreamMock->expects($this->once())->method('lock');
        $errorStreamMock->expects($this->once())->method('writeCsv')->with($fileData[0]);
        $errorStreamMock->expects($this->once())->method('unlock');
        $errorStreamMock->expects($this->once())->method('close');
        $this->reportValidatorMock->expects($this->once())->method('getErrors')->willReturn($errors);
        $this->directoryMock->expects($this->once())->method('openFile')->with(
            $this->stringContains('/var/tmp' . $reportName),
            'w+'
        )->willReturn($errorStreamMock);
        $this->reportWriter->write($this->directoryMock, '/var/tmp');
    }

    /**
     * @return void
     */
    public function testWriteErrorFile()
    {
        $reportName = 'testProvider';
        $providerClass = 'Magento\Analytics\Provider';
        $errors = ['orders', 'SQL Error: test'];
        $this->configInterfaceMock->expects($this->once())->method('get')->willReturn([
            [
                'providers' => [
                    [
                        [
                            'name' => $reportName,
                            'class' => $providerClass
                        ]
                    ]
                ]
            ]
        ]);
        $errorStreamMock = $this->getMockBuilder(
            \Magento\Framework\Filesystem\File\WriteInterface::class
        )->getMockForAbstractClass();
        $errorStreamMock->expects($this->once())->method('lock');
        $errorStreamMock->expects($this->once())->method('writeCsv')->with($errors);
        $errorStreamMock->expects($this->once())->method('unlock');
        $errorStreamMock->expects($this->once())->method('close');
        $this->reportValidatorMock->expects($this->once())->method('getErrors')->willReturn($errors);
        $this->directoryMock->expects($this->once())->method('openFile')->with('/var/tmp' . 'errors.csv', 'w+')
            ->willReturn($errorStreamMock);
        $this->reportWriter->write($this->directoryMock, '/var/tmp');
    }

    /**
     * @return void
     */
    public function testWriteEmptyReports()
    {
        $this->configInterfaceMock->expects($this->once())->method('get')->willReturn([]);
        $this->reportValidatorMock->expects($this->never())->method('getErrors');
        $this->directoryMock->expects($this->never())->method('openFile');
        $this->reportWriter->write($this->directoryMock, '/var/tmp');
    }
}
