<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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

    public function setUp(): void
    {
        $this->jobStaticRegenerate = $this->getJobStaticRegenerateMock(
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
     * @covers \Magento\Setup\Model\Cron\JobStaticRegenerate::execute
     */
    public function testExecuteProductionMode()
    {
        $modeObjectMock = $this->getModeObjectMock(['getMode']);
        $modeObjectMock->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue(State::MODE_PRODUCTION));

        $filesystemMock = $this->getFilesystemObjectMock(['regenerateStatic']);
        $filesystemMock
            ->expects($this->once())
            ->method('regenerateStatic');

        $this->jobStaticRegenerate
            ->expects($this->once())
            ->method('getFilesystem')
            ->will($this->returnValue($filesystemMock));

        $this->jobStaticRegenerate
            ->expects($this->once())
            ->method('getModeObject')
            ->will($this->returnValue($modeObjectMock));

        $this->jobStaticRegenerate
            ->expects($this->once())
            ->method('getOutputObject')
            ->will($this->returnValue($this->getOutputObjectMock()));

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
            ->will($this->returnValue(State::MODE_DEVELOPER));

        $this->jobStaticRegenerate
            ->expects($this->once())
            ->method('getModeObject')
            ->will($this->returnValue($modeObjectMock));

        $statusObject = $this->getStatusObjectMock(['add']);
        $statusObject
            ->expects($this->exactly(3))
            ->method('add');
        $this->jobStaticRegenerate
            ->expects($this->exactly(3))
            ->method('getStatusObject')
            ->will($this->returnValue($statusObject));

        $cacheObject = $this->getCacheObjectMock(['clean']);
        $cacheObject
            ->expects($this->once())
            ->method('clean');
        $this->jobStaticRegenerate
            ->expects($this->once())
            ->method('getCacheObject')
            ->will($this->returnValue($cacheObject));

        $cleanFilesObject = $this->getCleanFilesObjectMock(['clearMaterializedViewFiles', 'clearCodeGeneratedFiles']);
        $cleanFilesObject
            ->expects($this->once())
            ->method('clearMaterializedViewFiles');
        $cleanFilesObject
            ->expects($this->once())
            ->method('clearCodeGeneratedFiles');
        $this->jobStaticRegenerate
            ->expects($this->exactly(2))
            ->method('getCleanFilesObject')
            ->will($this->returnValue($cleanFilesObject));

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
            ->will($this->throwException(new \Exception('error')));
        $this->jobStaticRegenerate
            ->expects($this->once())
            ->method('getModeObject')
            ->will($this->returnValue($modeObjectMock));

        $statusObject = $this->getStatusObjectMock(['toggleUpdateError']);
        $statusObject
            ->expects($this->once())
            ->method('toggleUpdateError');
        $this->jobStaticRegenerate
            ->expects($this->once())
            ->method('getStatusObject')
            ->will($this->returnValue($statusObject));

        $this->jobStaticRegenerate->execute();
    }

    /**
     * Gets JobStaticRegenerate mock
     *
     * @return MockObject|\Magento\Setup\Model\Cron\JobStaticRegenerate
     */
    protected function getJobStaticRegenerateMock($methods = null)
    {
        return $this->createPartialMock(JobStaticRegenerate::class, $methods);
    }

    /**
     * Gets ObjectManagerProvider mock
     *
     * @return MockObject|Filesystem
     */
    protected function getFilesystemObjectMock($methods = null)
    {
        return $this->createPartialMock(Filesystem::class, $methods);
    }

    /**
     * Gets status object mock
     *
     * @return MockObject|Status
     */
    protected function getStatusObjectMock($methods = null)
    {
        return $this->createPartialMock(Status::class, $methods);
    }

    /**
     * Gets clean files object mock
     *
     * @return MockObject|CleanupFiles
     */
    protected function getCleanFilesObjectMock($methods = null)
    {
        return $this->createPartialMock(CleanupFiles::class, $methods);
    }

    /**
     * Gets cache object mock
     *
     * @return MockObject|CleanupFiles
     */
    protected function getCacheObjectMock($methods = null)
    {
        return $this->createPartialMock(CleanupFiles::class, $methods);
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
    protected function getModeObjectMock($methods = null)
    {
        return $this->createPartialMock(Mode::class, $methods);
    }
}
