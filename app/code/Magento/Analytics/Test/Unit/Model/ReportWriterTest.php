<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;

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
     * @var DirectoryWriteInterface|MockObject
     */
    private $directoryMock;

    /**
     * @var ReportWriter
     */
    private $reportWriter;

    protected function setUp(): void
    {
        $this->configInterfaceMock = $this->createMock(ConfigInterface::class);
        $this->reportValidatorMock = $this->createMock(ReportValidator::class);
        $this->providerFactoryMock = $this->createMock(ProviderFactory::class);
        $this->reportProviderMock = $this->createMock(ReportProvider::class);
        $this->directoryMock = $this->createMock(DirectoryWriteInterface::class);

        $this->reportWriter = new ReportWriter(
            $this->configInterfaceMock,
            $this->reportValidatorMock,
            $this->providerFactoryMock,
        );
    }

    /**
     * @param array $fileData
     * @param array $expectedFileData
     */
    #[
        TestWith([
            [[['number' => 1, 'type' => 'Shoes\"" Usual\\\\"']]],
            [['number' => 1, 'type' => 'Shoes"" Usual"']],
        ]),
        TestWith([
            [[['number' => 1, 'type' => 'hello "World"']]],
            [['number' => 1, 'type' => 'hello "World"']],
        ]),
        TestWith([
            [[['number' => 1, 'type' => 'hello \"World\"']]],
            [['number' => 1, 'type' => 'hello "World"']],
        ]),
        TestWith([
            [[['number' => 1, 'type' => 'hello \\"World\\"']]],
            [['number' => 1, 'type' => 'hello "World"']],
        ]),
        TestWith([
            [[['number' => 1, 'type' => 'hello \\\"World\\\"']]],
            [['number' => 1, 'type' => 'hello "World"']],
        ]),
        TestWith([
            [
                [['number' => 1, 'type' => 'hello World 1']],
                [['number' => 2, 'type' => 'hello World 2']],
            ],
            [
                ['number' => 1, 'type' => 'hello World 1'],
                ['number' => 2, 'type' => 'hello World 2'],
            ],
        ]),
    ]
    public function testWrite(array $fileData, array $expectedFileData): void
    {
        $fileData[] = [];
        $dataBatches = array_map(fn (array $batch) => new \IteratorIterator(new \ArrayIterator($batch)), $fileData);
        array_unshift($expectedFileData, ['number', 'type']);

        $configData = [];
        $providerClass = 'Magento\Analytics\Provider';
        $configData['providers'] = [
            [
                'name' => 'testProvider',
                'class' => $providerClass,
                'parameters' => ['name' => 'testReport'],
            ],
        ];

        $this->configInterfaceMock->expects($this->once())->method('get')->with()->willReturn([$configData]);
        $this->providerFactoryMock->expects($this->once())
            ->method('create')
            ->with($providerClass)
            ->willReturn($this->reportProviderMock);
        $parameterName = isset(reset($configData)[0]['parameters']['name'])
            ? reset($configData)[0]['parameters']['name']
            : '';
        $this->reportProviderMock->expects($this->exactly(count($dataBatches)))
            ->method('getBatchReport')
            ->with($parameterName ?: null)
            ->willReturnOnConsecutiveCalls(...$dataBatches);
        $errorStreamMock = $this->createMock(FileWriteInterface::class);
        $errorStreamMock->expects($this->once())->method('lock')->with()->willReturn(true);
        $errorStreamMock->expects($this->exactly(count($dataBatches))) //count of batches - empty batch + headers
            ->method('writeCsv')
            ->willReturnCallback(function (array $row) use ($expectedFileData) {
                static $index = 0;
                $this->assertEquals($expectedFileData[$index++], $row);
                return true;
            });

        $errorStreamMock->expects($this->once())->method('unlock');
        $errorStreamMock->expects($this->once())->method('close');
        if ($parameterName) {
            $this->reportValidatorMock->expects($this->once())
                ->method('validate')
                ->with($parameterName)
                ->willReturn([]);
        }
        $this->directoryMock->expects($this->once())
            ->method('openFile')
            ->with($this->stringContains('/var/tmp' . $parameterName), 'w+')
            ->willReturn($errorStreamMock);
        $this->assertTrue($this->reportWriter->write($this->directoryMock, '/var/tmp'));
    }

    /**
     * @param array $configData
     * @return void
     */
    #[DataProvider('writeErrorFileDataProvider')]
    public function testWriteErrorFile(array $configData): void
    {
        $errors = ['orders', 'SQL Error: test'];
        $this->configInterfaceMock->expects($this->once())->method('get')->willReturn([$configData]);
        $errorStreamMock = $this->createMock(FileWriteInterface::class);
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
    public function testWriteEmptyReports(): void
    {
        $this->configInterfaceMock->expects($this->once())->method('get')->willReturn([]);
        $this->reportValidatorMock->expects($this->never())->method('validate');
        $this->directoryMock->expects($this->never())->method('openFile');
        $this->assertTrue($this->reportWriter->write($this->directoryMock, '/var/tmp'));
    }

    /**
     * @return array
     */
    public static function writeErrorFileDataProvider(): array
    {
        return [
            [
                'configData' => [
                    'providers' => [
                        [
                            'name' => 'testProvider',
                            'class' => 'Magento\Analytics\Provider',
                            'parameters' => ['name' => 'testReport'],
                        ]
                    ]
                ],
            ],
        ];
    }
}
