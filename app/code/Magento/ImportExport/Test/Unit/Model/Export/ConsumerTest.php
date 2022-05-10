<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Notification\NotifierInterface;
use Magento\ImportExport\Api\Data\LocalizedExportInfoInterface;
use Magento\ImportExport\Api\ExportManagementInterface;
use Magento\ImportExport\Model\Export\Consumer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ConsumerTest extends TestCase
{
    /**
     * @var MockObject|LoggerInterface
     */
    private $loggerMock;

    /**
     * @var ExportManagementInterface|MockObject
     */
    private $exportManagementMock;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;

    /**
     * @var NotifierInterface|MockObject
     */
    private $notifierMock;

    /**
     * @var ResolverInterface|MockObject
     */
    private $localeResolver;

    /**
     * @var Consumer
     */
    private $consumer;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->exportManagementMock = $this->createMock(ExportManagementInterface::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->notifierMock = $this->createMock(NotifierInterface::class);
        $this->localeResolver = $this->createMock(ResolverInterface::class);
        $this->consumer = new Consumer(
            $this->loggerMock,
            $this->exportManagementMock,
            $this->filesystemMock,
            $this->notifierMock,
            $this->localeResolver
        );
    }

    public function testProcess()
    {
        $adminLocale = 'de_DE';
        $exportInfoMock = $this->createMock(LocalizedExportInfoInterface::class);
        $exportInfoMock->expects($this->atLeastOnce())
            ->method('getLocale')
            ->willReturn($adminLocale);
        $exportInfoMock->expects($this->atLeastOnce())
            ->method('getFileName')
            ->willReturn('file_name.csv');

        $defaultLocale = 'en_US';
        $this->localeResolver->expects($this->once())
            ->method('getLocale')
            ->willReturn($defaultLocale);
        $this->localeResolver->expects($this->exactly(2))
            ->method('setLocale')
            ->withConsecutive([$adminLocale], [$defaultLocale])
            ->willReturn($this->localeResolver);

        $data = '1,2,3';
        $this->exportManagementMock->expects($this->once())
            ->method('export')
            ->with($exportInfoMock)
            ->willReturn($data);

        $directoryMock = $this->createMock(WriteInterface::class);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::VAR_IMPORT_EXPORT)
            ->willReturn($directoryMock);
        $directoryMock->expects($this->once())
            ->method('writeFile')
            ->with('export/file_name.csv', $data)
            ->willReturn(5);

        $this->notifierMock->expects($this->once())
            ->method('addMajor')
            ->willReturn($this->notifierMock);

        $this->consumer->process($exportInfoMock);
    }
}
