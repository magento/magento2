<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Framework\Exception\FileSystemException;
use Magento\Setup\Model\Cron\Status;

class StatusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Status
     */
    private $status;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $varReaderWriter;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Setup\Model\Cron\SetupLoggerFactory
     */
    private $setupLoggerFactory;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->varReaderWriter = $this->getMockForAbstractClass(
            \Magento\Framework\Filesystem\Directory\WriteInterface::class,
            [],
            '',
            false
        );
        $this->filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($this->varReaderWriter);
        $this->logger = $this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class, [], '', false);
        $this->setupLoggerFactory =
            $this->createMock(\Magento\Setup\Model\Cron\SetupLoggerFactory::class);
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
        $this->logger->expects($this->once())->method('log')->with(\Psr\Log\LogLevel::ERROR, 'test1');
        $this->status->add('test1', \Psr\Log\LogLevel::ERROR);
    }

    public function testToggleUpdateInProgressTrue()
    {
        $this->varReaderWriter->expects($this->once())->method('touch');
        $this->status->toggleUpdateInProgress(true);
    }

    /**
     */
    public function testToggleUpdateInProgressTrueException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('".update_in_progress.flag" cannot be created');

        $this->varReaderWriter->expects($this->once())
            ->method('touch')
            ->willThrowException(new FileSystemException(new \Magento\Framework\Phrase('Exception')));
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

    /**
     */
    public function testToggleUpdateErrorTrueException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('".update_error.flag" cannot be created');

        $this->varReaderWriter->expects($this->once())
            ->method('touch')
            ->willThrowException(new FileSystemException(new \Magento\Framework\Phrase('Exception')));
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
    return;
}
