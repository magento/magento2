<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Ui\DataProvider;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\ImportExport\Model\Export\ConfigInterface;
use Magento\ImportExport\Model\Export\FileInfo;
use Magento\ImportExport\Ui\DataProvider\ExportFileDataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExportFileDataProviderTest extends TestCase
{
    /**
     * @var WriteInterface|MockObject
     */
    private $directoryMock;

    /**
     * @var File|MockObject
     */
    private $fileIOMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ExportFileDataProvider
     */
    private ExportFileDataProvider $model;

    protected function setUp(): void
    {
        $reportingMock = $this->createMock(ReportingInterface::class);
        $searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $filterBuilderMock = $this->createMock(FilterBuilder::class);
        $fileMock = $this->createMock(DriverInterface::class);
        $filesystemMock = $this->createMock(Filesystem::class);
        $this->directoryMock = $this->createMock(WriteInterface::class);
        $filesystemMock->method('getDirectoryWrite')
            ->willReturn($this->directoryMock);
        $this->fileIOMock = $this->createMock(File::class);
        $fileInfoIoMock = $this->createMock(File::class);
        $fileInfoIoMock->method('getPathInfo')
            ->willReturnCallback(
                fn ($path) => [
                    'extension' => pathinfo($path, PATHINFO_EXTENSION),
                ]
            );

        $this->model = new ExportFileDataProvider(
            'export_grid_data_source',
            'file_name',
            'file_name',
            $reportingMock,
            $searchCriteriaBuilderMock,
            $this->requestMock,
            $filterBuilderMock,
            $fileMock,
            $filesystemMock,
            $this->fileIOMock,
            new FileInfo(
                $this->createConfiguredMock(ConfigInterface::class, ['getFileFormats' => ['csv' => []]]),
                $fileInfoIoMock
            )
        );
    }

    public function testGetData(): void
    {
        $this->directoryMock->method('getAbsolutePath')
            ->willReturnCallback(fn ($path) => $path ?: '/var/');
        $this->directoryMock->expects(self::once())
            ->method('isExist')
            ->with('/var/export/')
            ->willReturn(true);
        $driverMock = $this->createMock(DriverInterface::class);
        $this->directoryMock->method('getDriver')
            ->willReturn($driverMock);
        $files = [
            '/var/export/file1.csv' => ['mtime' => 1000000001],
            '/var/export/file2.csv' => ['mtime' => 1000000002],
            '/var/export/file3.csv' => ['mtime' => 1000000002],
            '/var/export/file4.csv' => ['mtime' => 1000000003],
            '/var/export/file5.csv.tmp' => ['mtime' => 1000000004],
            '/var/export/.DS_Store' => ['mtime' => 1000000005],
            '/var/export/preview.jpg' => ['mtime' => 1000000006],
        ];
        $driverMock->expects(self::once())
            ->method('readDirectoryRecursively')
            ->with('/var/export/')
            ->willReturn(array_keys($files));
        $this->directoryMock->expects(self::exactly(count($files)))
            ->method('isFile')
            ->willReturn(true);
        $this->directoryMock->method('stat')
            ->willReturnCallback(fn ($path) => $files[$path]);
        $this->fileIOMock->expects(self::exactly(count($files)))
            ->method('getPathInfo')
            ->willReturnCallback(
                fn ($path) => [
                    'dirname' => '/var/export',
                    'basename' => str_replace('/var/export/', '', $path),
                    'extension' => pathinfo($path, PATHINFO_EXTENSION),
                    'filename' => pathinfo($path, PATHINFO_FILENAME),
                ]
            );
        $this->requestMock->method('getParam')
            ->with('paging')
            ->willReturn(['pageSize' => 10, 'current' => 1]);

        $data = $this->model->getData();
        self::assertEquals(4, $data['totalRecords']);
        self::assertEquals(
            ['file4.csv', 'file2.csv', 'file3.csv', 'file1.csv'],
            array_column($data['items'], 'file_name')
        );
    }

    public function testGetDataReturnsEmptyWhenAllFilesAreInProgress(): void
    {
        $this->directoryMock->method('getAbsolutePath')
            ->willReturnCallback(fn ($path) => $path ?: '/var/');
        $this->directoryMock->expects(self::once())
            ->method('isExist')
            ->with('/var/export/')
            ->willReturn(true);
        $driverMock = $this->createMock(DriverInterface::class);
        $this->directoryMock->method('getDriver')
            ->willReturn($driverMock);
        $files = [
            '/var/export/file1.csv.tmp' => ['mtime' => 1000000001],
            '/var/export/file2.csv.tmp' => ['mtime' => 1000000002],
        ];
        $driverMock->expects(self::once())
            ->method('readDirectoryRecursively')
            ->with('/var/export/')
            ->willReturn(array_keys($files));
        $this->directoryMock->expects(self::exactly(count($files)))
            ->method('isFile')
            ->willReturn(true);
        $this->directoryMock->method('stat')
            ->willReturnCallback(fn ($path) => $files[$path]);
        $this->fileIOMock->expects(self::exactly(count($files)))
            ->method('getPathInfo')
            ->willReturnCallback(
                fn ($path) => [
                    'dirname' => '/var/export',
                    'basename' => str_replace('/var/export/', '', $path),
                    'extension' => pathinfo($path, PATHINFO_EXTENSION),
                    'filename' => pathinfo($path, PATHINFO_FILENAME),
                ]
            );

        $data = $this->model->getData();
        self::assertEquals(0, $data['totalRecords']);
        self::assertEmpty($data['items']);
    }
}
