<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Phrase;
use Magento\Setup\Model\Cron\SetupLoggerFactory;
use Magento\Setup\Model\Cron\Status;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class StatusTest extends TestCase
{
    /**
     * @var MockObject|Status
     */
    private $status;

    /**
     * @var MockObject|Filesystem
     */
    private $filesystem;

    /**
     * @var MockObject|WriteInterface
     */
    private $varReaderWriter;

    /**
     * @var MockObject|LoggerInterface
     */
    private $logger;

    /**
     * @var MockObject|SetupLoggerFactory
     */
    private $setupLoggerFactory;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->varReaderWriter = $this->getMockForAbstractClass(
            WriteInterface::class,
            [],
            '',
            false
        );
        $this->filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($this->varReaderWriter);
        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class, [], '', false);
        $this->setupLoggerFactory =
            $this->createMock(SetupLoggerFactory::class);
        $this->setupLoggerFactory
            ->expects($this->once())
            ->method('create')
            ->with('setup-cron')
            ->willReturn($this->logger);
        $this->status = new Status($this->filesystem, $this->setupLoggerFactory);
    }

    public function testGetStatusFilePath()
    {
        $this->varReaderWriter->expects($this->any())
            ->method('getAbsolutePath')
            ->with('.update_status.txt')
            ->willReturn('DIR/var/.update_status.txt');
        $this->assertEquals('DIR/var/.update_status.txt', $this->status->getStatusFilePath());
    }

    public function testGetLogFilePath()
    {
        $this->varReaderWriter->expects($this->any())
            ->method('getAbsolutePath')
            ->with('log/update.log')
            ->willReturn('DIR/var/log/update.log');
        $this->assertEquals('DIR/var/log/update.log', $this->status->getLogFilePath());
    }

    public function testAdd()
    {
        $this->varReaderWriter->expects($this->once())->method('isExist')->willReturn(false);
        $this->varReaderWriter->expects($this->once())->method('writeFile');
        $this->logger->expects($this->once())->method('log')->with(LogLevel::ERROR, 'test1');
        $this->status->add('test1', LogLevel::ERROR);
    }

    public function testToggleUpdateInProgressTrue()
    {
        $this->varReaderWriter->expects($this->once())->method('touch');
        $this->status->toggleUpdateInProgress(true);
    }

    public function testToggleUpdateInProgressTrueException()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('".update_in_progress.flag" cannot be created');
        $this->varReaderWriter->expects($this->once())
            ->method('touch')
            ->willThrowException(new FileSystemException(new Phrase('Exception')));
        $this->status->toggleUpdateInProgress(true);
    }

    public function testToggleUpdateInProgressFalseFlagExist()
    {
        $this->varReaderWriter->expects($this->at(0))->method('isExist')->willReturn(true);
        $this->varReaderWriter->expects($this->at(1))->method('delete');
        $this->status->toggleUpdateInProgress(false);
    }

    public function testToggleUpdateInProgressFalseFlagNotExist()
    {
        $this->varReaderWriter->expects($this->at(0))->method('isExist')->willReturn(false);
        $this->varReaderWriter->expects($this->never())->method('delete');
        $this->status->toggleUpdateInProgress(false);
    }

    public function testToggleUpdateErrorTrue()
    {
        $this->varReaderWriter->expects($this->once())->method('touch');
        $this->status->toggleUpdateError(true);
    }

    public function testToggleUpdateErrorTrueException()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('".update_error.flag" cannot be created');
        $this->varReaderWriter->expects($this->once())
            ->method('touch')
            ->willThrowException(new FileSystemException(new Phrase('Exception')));
        $this->status->toggleUpdateError(true);
    }

    public function testToggleUpdateErrorFalseFlagExist()
    {
        $this->varReaderWriter->expects($this->at(0))->method('isExist')->willReturn(true);
        $this->varReaderWriter->expects($this->at(1))->method('delete');
        $this->status->toggleUpdateError(false);
    }

    public function testToggleUpdateErrorFalseFlagNotExist()
    {
        $this->varReaderWriter->expects($this->at(0))->method('isExist')->willReturn(false);
        $this->varReaderWriter->expects($this->never())->method('delete');
        $this->status->toggleUpdateError(false);
    }

    public function testIsUpdateError()
    {
        $this->varReaderWriter->expects($this->once())->method('isExist')->willReturn(true);
        $this->assertTrue($this->status->isUpdateError());
    }
}

namespace Magento\Setup\Model\Cron;

function chmod()
{
}
