<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model;

use Magento\Deploy\Model\ProcessManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Deploy\Model\ProcessTaskFactory;
use Magento\Deploy\Model\ProcessTask;

class ProcessQueueManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Deploy\Model\ProcessQueueManager
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Deploy\Model\ProcessManager
     */
    private $processManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ResourceConnection
     */
    private $resourceConnectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ProcessTaskFactory
     */
    private $processTaskFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ProcessTask
     */
    private $processTaskMock;

    protected function setUp()
    {
        $this->processManagerMock = $this->getMock(ProcessManager::class, [], [], '', false);
        $this->resourceConnectionMock = $this->getMock(ResourceConnection::class, [], [], '', false);
        $this->processTaskFactoryMock = $this->getMock(ProcessTaskFactory::class, ['create'], [], '', false);
        $this->processTaskMock = $this->getMock(ProcessTask::class, [], [], '', false);
        $this->processTaskFactoryMock->expects($this->any())->method('create')->willReturn($this->processTaskMock);
        $this->model = (new ObjectManager($this))->getObject(
            \Magento\Deploy\Model\ProcessQueueManager::class,
            [
                'processManager' => $this->processManagerMock,
                'resourceConnection' => $this->resourceConnectionMock,
                'processTaskFactory' => $this->processTaskFactoryMock
            ]
        );
    }

    public function testProcess()
    {
        $callableMock = function () {
            return true;
        };
        $this->processTaskMock->expects($this->any())->method('getHandler')->willReturn($callableMock);

        $processMock = $this->getMock(\Magento\Deploy\Model\Process::class, [], [], '', false);

        $this->model->addTaskToQueue($callableMock, []);
        $this->processManagerMock->expects($this->atLeastOnce())->method('getProcesses')->willReturnOnConsecutiveCalls(
            [$processMock],
            [$processMock],
            [$processMock],
            [$processMock],
            [$processMock],
            []
        );
        $processMock->expects($this->once())->method('isCompleted')->willReturn(true);
        $processMock->expects($this->atLeastOnce())->method('getPid')->willReturn(42);
        $processMock->expects($this->once())->method('getStatus')->willReturn(0);
        $this->processManagerMock->expects($this->once())->method('delete')->with($processMock);

        $this->resourceConnectionMock->expects(self::once())->method('closeConnection');

        $this->assertEquals(0, $this->model->process());
    }
}
