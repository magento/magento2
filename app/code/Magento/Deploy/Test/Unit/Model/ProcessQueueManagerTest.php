<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model;

use Magento\Deploy\Model\Process\ResourceConnectionProvider;
use Magento\Deploy\Model\ProcessManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

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
    private $resourceConnectionProviderMock;

    protected function setUp()
    {
        $this->processManagerMock = $this->getMock(ProcessManager::class, [], [], '', false);
        $this->resourceConnectionProviderMock = $this->getMock(ResourceConnectionProvider::class, [], [], '', false);
        $this->model = (new ObjectManager($this))->getObject(
            \Magento\Deploy\Model\ProcessQueueManager::class,
            [
                'processManager' => $this->processManagerMock,
                'resourceConnectionProvider' => $this->resourceConnectionProviderMock,
            ]
        );
    }

    public function testProcess()
    {
        $callableMock = function () {
            return true;
        };

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

        $resourceConnectionMock = $this->getMock(ResourceConnection::class, [], [], '', false);
        $resourceConnectionMock->expects(self::once())->method('closeConnection');
        $this->resourceConnectionProviderMock->expects($this->once())->method('get')
            ->willReturn($resourceConnectionMock);

        $this->assertEquals(0, $this->model->process());
    }
}
