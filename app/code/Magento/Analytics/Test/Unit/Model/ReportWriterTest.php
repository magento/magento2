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
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var string
     */
    private $reportName = 'testReport';

    /**
     * @var string
     */
    private $providerName = 'testProvider';

    /**
     * @var string
     */
    private $providerClass = 'Magento\Analytics\Provider';

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
     * @param array $configData
     * @return void
     *
     * @dataProvider configDataProvider
     */
    public function testWrite($configData)
    {
        $errors = [];
        $fileData = [
            [1, 'Shoes Usual']
        ];
        $this->configInterfaceMock
            ->expects($this->once())
            ->method('get')
            ->with()
            ->willReturn([$configData]);
        $this->providerFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($this->providerClass)
            ->willReturn($this->reportProviderMock);
        $parameterName = isset(reset($configData)[0]['parameters']['name'])
            ? reset($configData)[0]['parameters']['name']
            : '';
        $this->reportProviderMock->expects($this->once())
            ->method('getReport')
            ->with($parameterName ?: null)
            ->willReturn($fileData);
        $errorStreamMock = $this->getMockBuilder(
            \Magento\Framework\Filesystem\File\WriteInterface::class
        )->getMockForAbstractClass();
        $errorStreamMock->expects($this->once())->method('lock');
        $errorStreamMock->expects($this->once())->method('writeCsv')->with($fileData[0]);
        $errorStreamMock->expects($this->once())->method('unlock');
        $errorStreamMock->expects($this->once())->method('close');
        if ($parameterName) {
            $this->reportValidatorMock
                ->expects($this->once())
                ->method('validate')
                ->with($parameterName)
                ->willReturn($errors);
        }
        $this->directoryMock
            ->expects($this->once())
            ->method('openFile')
            ->with(
                $this->stringContains('/var/tmp' . $parameterName ?: $this->reportName),
                'w+'
            )->willReturn($errorStreamMock);
        $this->reportWriter->write($this->directoryMock, '/var/tmp');
    }

    /**
     * @param array $configData
     * @return void
     *
     * @dataProvider configDataProvider
     */
    public function testWriteErrorFile($configData)
    {
        $errors = ['orders', 'SQL Error: test'];
        $this->configInterfaceMock->expects($this->once())->method('get')->willReturn([$configData]);
        $errorStreamMock = $this->getMockBuilder(
            \Magento\Framework\Filesystem\File\WriteInterface::class
        )->getMockForAbstractClass();
        $errorStreamMock->expects($this->once())->method('lock');
        $errorStreamMock->expects($this->once())->method('writeCsv')->with($errors);
        $errorStreamMock->expects($this->once())->method('unlock');
        $errorStreamMock->expects($this->once())->method('close');
        $this->reportValidatorMock->expects($this->once())->method('validate')->willReturn($errors);
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
        $this->reportValidatorMock->expects($this->never())->method('validate');
        $this->directoryMock->expects($this->never())->method('openFile');
        $this->reportWriter->write($this->directoryMock, '/var/tmp');
    }

    /**
     * @return array
     */
    public function configDataProvider()
    {
        return [
            'reportProvider' => [
                [
                    'providers' => [
                        [
                            'name' => $this->providerName,
                            'class' => $this->providerClass,
                            'parameters' => [
                                'name' => $this->reportName
                            ],
                        ]
                    ]
                ]
            ],
        ];
    }
}
