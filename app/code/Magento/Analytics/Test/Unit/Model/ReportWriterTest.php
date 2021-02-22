<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\ConfigInterface;
use Magento\Analytics\Model\ProviderFactory;
use Magento\Analytics\Model\ReportWriter;
use Magento\Analytics\ReportXml\DB\ReportValidator;
use Magento\Analytics\ReportXml\ReportProvider;
use Magento\Framework\Filesystem\Directory\WriteInterface as DirectoryWriteInterface;
use Magento\Framework\Filesystem\File\WriteInterface as FileWriteInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReportWriterTest extends TestCase
{
    /**
     * @var ConfigInterface|MockObject
     */
    private $configInterfaceMock;

    /**
     * @var ReportValidator|MockObject
     */
    private $reportValidatorMock;

    /**
     * @var ProviderFactory|MockObject
     */
    private $providerFactoryMock;

    /**
     * @var ReportProvider|MockObject
     */
    private $reportProviderMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var DirectoryWriteInterface|MockObject
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
    protected function setUp(): void
    {
        $this->configInterfaceMock = $this->getMockBuilder(ConfigInterface::class)->getMockForAbstractClass();
        $this->reportValidatorMock = $this->getMockBuilder(ReportValidator::class)
            ->disableOriginalConstructor()->getMock();
        $this->providerFactoryMock = $this->getMockBuilder(ProviderFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->reportProviderMock = $this->getMockBuilder(ReportProvider::class)
            ->disableOriginalConstructor()->getMock();
        $this->directoryMock = $this->getMockBuilder(DirectoryWriteInterface::class)->getMockForAbstractClass();
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
     * @param array $fileData
     * @param array $expectedFileData
     * @return void
     *
     * @dataProvider configDataProvider
     */
    public function testWrite(array $configData, array $fileData, array $expectedFileData)
    {
        $errors = [];
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
            FileWriteInterface::class
        )->getMockForAbstractClass();
        $errorStreamMock
            ->expects($this->once())
            ->method('lock')
            ->with();
        $errorStreamMock
            ->expects($this->exactly(2))
            ->method('writeCsv')
            ->withConsecutive(
                [array_keys($expectedFileData[0])],
                [$expectedFileData[0]]
            );
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
        $this->assertTrue($this->reportWriter->write($this->directoryMock, '/var/tmp'));
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
            FileWriteInterface::class
        )->getMockForAbstractClass();
        $errorStreamMock->expects($this->once())->method('lock');
        $errorStreamMock->expects($this->once())->method('writeCsv')->with($errors);
        $errorStreamMock->expects($this->once())->method('unlock');
        $errorStreamMock->expects($this->once())->method('close');
        $this->reportValidatorMock->expects($this->once())->method('validate')->willReturn($errors);
        $this->directoryMock->expects($this->once())->method('openFile')->with('/var/tmp' . 'errors.csv', 'w+')
            ->willReturn($errorStreamMock);
        $this->assertTrue($this->reportWriter->write($this->directoryMock, '/var/tmp'));
    }

    /**
     * @return void
     */
    public function testWriteEmptyReports()
    {
        $this->configInterfaceMock->expects($this->once())->method('get')->willReturn([]);
        $this->reportValidatorMock->expects($this->never())->method('validate');
        $this->directoryMock->expects($this->never())->method('openFile');
        $this->assertTrue($this->reportWriter->write($this->directoryMock, '/var/tmp'));
    }

    /**
     * @return array
     */
    public function configDataProvider()
    {
        return [
            'reportProvider' => [
                'configData' => [
                    'providers' => [
                        [
                            'name' => $this->providerName,
                            'class' => $this->providerClass,
                            'parameters' => [
                                'name' => $this->reportName
                            ],
                        ]
                    ]
                ],
                'fileData' => [
                    ['number' => 1, 'type' => 'Shoes\"" Usual\\\\"']
                ],
                'expectedFileData' => [
                    ['number' => 1, 'type' => 'Shoes\"\" Usual\\"']
                ]
            ],
        ];
    }
}
