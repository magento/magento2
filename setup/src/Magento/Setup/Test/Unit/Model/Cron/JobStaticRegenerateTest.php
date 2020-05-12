<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Deploy\Model\Filesystem;
use Magento\Deploy\Model\Mode;
use Magento\Framework\App\State;
use Magento\Framework\App\State\CleanupFiles;
use Magento\Setup\Model\Cron\JobStaticRegenerate;
use Magento\Setup\Model\Cron\Status;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class JobStaticRegenerateTest extends TestCase
{
    /**
     * @var MockObject|\Magento\Setup\Model\Cron\JobStaticRegenerate
     */
    private $jobStaticRegenerate;

    protected function setUp(): void
    {
        $this->jobStaticRegenerate = $this->getJobStaticRegenerateMock();
    }

    /**
     * @covers \Magento\Setup\Model\Cron\JobStaticRegenerate::execute
     */
    public function testExecuteProductionMode()
    {
        $modeObjectMock = $this->getModeObjectMock();
        $modeObjectMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_PRODUCTION);

        $filesystemMock = $this->getFilesystemObjectMock();
        $filesystemMock
            ->expects($this->once())
            ->method('regenerateStatic');

        $this->jobStaticRegenerate
            ->expects($this->once())
            ->method('getFilesystem')
            ->willReturn($filesystemMock);

        $this->jobStaticRegenerate
            ->expects($this->once())
            ->method('getModeObject')
            ->willReturn($modeObjectMock);

        $this->jobStaticRegenerate
            ->expects($this->once())
            ->method('getOutputObject')
            ->willReturn($this->getOutputObjectMock());

        $this->jobStaticRegenerate->execute();
    }

    /**
     * @covers \Magento\Setup\Model\Cron\JobStaticRegenerate::execute
     */
    public function testExecuteDevelopernMode()
    {
        $modeObjectMock = $this->getModeObjectMock(['getMode']);
        $modeObjectMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);

        $this->jobStaticRegenerate
            ->expects($this->once())
            ->method('getModeObject')
            ->willReturn($modeObjectMock);

        $statusObject = $this->getStatusObjectMock();
        $statusObject
            ->expects($this->exactly(3))
            ->method('add');
        $this->jobStaticRegenerate
            ->expects($this->exactly(3))
            ->method('getStatusObject')
            ->willReturn($statusObject);

        $cacheObject = $this->getCacheObjectMock();
        $cacheObject
            ->expects($this->once())
            ->method('clean');
        $this->jobStaticRegenerate
            ->expects($this->once())
            ->method('getCacheObject')
            ->willReturn($cacheObject);

        $cleanFilesObject = $this->getCleanFilesObjectMock();
        $cleanFilesObject
            ->expects($this->once())
            ->method('clearMaterializedViewFiles');
        $cleanFilesObject
            ->expects($this->once())
            ->method('clearCodeGeneratedFiles');
        $this->jobStaticRegenerate
            ->expects($this->exactly(2))
            ->method('getCleanFilesObject')
            ->willReturn($cleanFilesObject);

        $this->jobStaticRegenerate->execute();
    }

    /**
     * @covers \Magento\Setup\Model\Cron\JobStaticRegenerate::execute
     */
    public function testExecuteWithException()
    {
        $this->expectException('RuntimeException');
        $modeObjectMock = $this->getModeObjectMock(['getMode']);
        $modeObjectMock->expects($this->once())
            ->method('getMode')
            ->willThrowException(new \Exception('error'));
        $this->jobStaticRegenerate
            ->expects($this->once())
            ->method('getModeObject')
            ->willReturn($modeObjectMock);

        $statusObject = $this->getStatusObjectMock();
        $statusObject
            ->expects($this->once())
            ->method('toggleUpdateError');
        $this->jobStaticRegenerate
            ->expects($this->once())
            ->method('getStatusObject')
            ->willReturn($statusObject);

        $this->jobStaticRegenerate->execute();
    }

    /**
     * Gets JobStaticRegenerate mock
     *
     * @return MockObject|\Magento\Setup\Model\Cron\JobStaticRegenerate
     */
    protected function getJobStaticRegenerateMock()
    {
        return $this->createPartialMock(
            JobStaticRegenerate::class,
            [
                'getCacheObject',
                'getCleanFilesObject',
                'getStatusObject',
                'getOutputObject',
                'getModeObject',
                'getFilesystem',
            ]
        );
    }

    /**
     * Gets ObjectManagerProvider mock
     *
     * @return MockObject|Filesystem
     */
    protected function getFilesystemObjectMock()
    {
        return $this->createPartialMock(Filesystem::class, ['regenerateStatic']);
    }

    /**
     * Gets status object mock
     *
     * @return MockObject|Status
     */
    protected function getStatusObjectMock()
    {
        return $this->createPartialMock(Status::class, ['add', 'toggleUpdateError']);
    }

    /**
     * Gets clean files object mock
     *
     * @return MockObject|CleanupFiles
     */
    protected function getCleanFilesObjectMock()
    {
        return $this->createPartialMock(CleanupFiles::class, ['clearMaterializedViewFiles', 'clearCodeGeneratedFiles']);
    }

    /**
     * Gets cache object mock
     *
     * @return MockObject|CleanupFiles
     */
    protected function getCacheObjectMock()
    {
        return $this->getMockBuilder(CleanupFiles::class)
            ->addMethods(['clean'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Gets output object mock
     *
     * @return MockObject|OutputInterface
     */
    protected function getOutputObjectMock()
    {
        return $this->getMockForAbstractClass(OutputInterface::class);
    }

    /**
     * Gets mode mock
     *
     * @return MockObject|Mode
     */
    protected function getModeObjectMock()
    {
        return $this->createPartialMock(Mode::class, ['getMode']);
    }
}
