<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Framework\App\State;

class JobStaticRegenerateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Cron\JobStaticRegenerate
     */
    private $jobStaticRegenerate;

    public function setUp()
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
     * @expectedException \RuntimeException
     * @covers \Magento\Setup\Model\Cron\JobStaticRegenerate::execute
     */
    public function testExecuteWithException()
    {
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
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Cron\JobStaticRegenerate
     */
    protected function getJobStaticRegenerateMock($methods = null)
    {
        return $this->createPartialMock(\Magento\Setup\Model\Cron\JobStaticRegenerate::class, $methods);
    }

    /**
     * Gets ObjectManagerProvider mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Deploy\Model\Filesystem
     */
    protected function getFilesystemObjectMock($methods = null)
    {
        return $this->createPartialMock(\Magento\Deploy\Model\Filesystem::class, $methods);
    }

    /**
     * Gets status object mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Cron\Status
     */
    protected function getStatusObjectMock($methods = null)
    {
        return $this->createPartialMock(\Magento\Setup\Model\Cron\Status::class, $methods);
    }

    /**
     * Gets clean files object mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\State\CleanupFiles
     */
    protected function getCleanFilesObjectMock($methods = null)
    {
        return $this->createPartialMock(\Magento\Framework\App\State\CleanupFiles::class, $methods);
    }

    /**
     * Gets cache object mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\State\CleanupFiles
     */
    protected function getCacheObjectMock($methods = null)
    {
        return $this->createPartialMock(\Magento\Framework\App\State\CleanupFiles::class, $methods);
    }

    /**
     * Gets output object mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Console\Output\OutputInterface
     */
    protected function getOutputObjectMock()
    {
        return $this->getMockForAbstractClass(\Symfony\Component\Console\Output\OutputInterface::class);
    }

    /**
     * Gets mode mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Deploy\Model\Mode
     */
    protected function getModeObjectMock($methods = null)
    {
        return $this->createPartialMock(\Magento\Deploy\Model\Mode::class, $methods);
    }
}
