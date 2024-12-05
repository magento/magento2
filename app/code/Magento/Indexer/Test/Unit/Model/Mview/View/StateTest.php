<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model\Mview\View;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Indexer\Model\Mview\View\State;
use Magento\Indexer\Model\ResourceModel\Mview\View\State\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StateTest extends TestCase
{
    /**
     * @var State
     */
    protected $model;

    /**
     * @var Context|MockObject
     */
    protected $_contextMock;

    /**
     * @var Registry|MockObject
     */
    protected $_registryMock;

    /**
     * @var \Magento\Indexer\Model\ResourceModel\Mview\View\State|MockObject
     */
    protected $_resourceMock;

    /**
     * @var Collection|MockObject
     */
    protected $_resourceCollectionMock;

    /**
     * @var \Magento\Framework\Lock\LockManagerInterface|MockObject
     */
    protected $lockManagerMock;

    /**
     * @var \Magento\Framework\App\DeploymentConfig|MockObject
     */
    protected $configReaderMock;

    protected function setUp(): void
    {
        $this->_contextMock = $this->createPartialMock(Context::class, ['getEventDispatcher']);
        $eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->_contextMock->expects($this->any())->method('getEventDispatcher')->willReturn($eventManagerMock);
        $this->_registryMock = $this->createMock(Registry::class);
        $this->_resourceMock = $this->createMock(\Magento\Indexer\Model\ResourceModel\Mview\View\State::class);
        $this->_resourceCollectionMock = $this->createMock(Collection::class);
        $this->lockManagerMock = $this->createMock(\Magento\Framework\Lock\LockManagerInterface::class);
        $this->configReaderMock = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
        $this->model = new State(
            $this->_contextMock,
            $this->_registryMock,
            $this->_resourceMock,
            $this->_resourceCollectionMock,
            [],
            $this->lockManagerMock,
            $this->configReaderMock
        );
    }

    public function testLoadByView()
    {
        $viewId = 'view_id';
        $this->_resourceMock->expects($this->once())->method('load')->with($this->model, $viewId)->willReturnSelf();
        $this->model->loadByView($viewId);
        $this->assertEquals($viewId, $this->model->getViewId());
    }

    public function testBeforeSave()
    {
        $this->assertNull($this->model->getUpdated());
        $this->model->beforeSave();
        $this->assertNotNull($this->model->getUpdated());
    }

    public function testSetterAndGetterWithoutApplicationLock()
    {
        $this->configReaderMock->expects($this->any())->method('get')->willReturn(false);

        $this->lockManagerMock->expects($this->any())->method('isLocked')->willReturn(false);

        $mode = \Magento\Framework\Mview\View\StateInterface::MODE_ENABLED;
        $this->model->setMode($mode);
        $this->assertEquals($mode, $this->model->getMode());

        $status = \Magento\Framework\Mview\View\StateInterface::STATUS_WORKING;
        $this->model->setStatus($status);
        $this->assertEquals($status, $this->model->getStatus());

        $date = time();
        $this->model->setUpdated($date);
        $this->assertEquals($date, $this->model->getUpdated());

        $versionId = 99;
        $this->model->setVersionId($versionId);
        $this->assertEquals($versionId, $this->model->getVersionId());
    }

    /**
     * @return array
     */
    public static function executeProvider()
    {
        return [
            [
                'setStatus' => \Magento\Framework\Mview\View\StateInterface::STATUS_WORKING,
                'getStatus' => \Magento\Framework\Mview\View\StateInterface::STATUS_WORKING,
                'lock' => 'lock',
                'isLocked' => true
            ],
            [
                'setStatus' => \Magento\Framework\Mview\View\StateInterface::STATUS_WORKING,
                'getStatus' => \Magento\Framework\Mview\View\StateInterface::STATUS_IDLE,
                'lock' => 'lock',
                'isLocked' => false
            ],
            [
                'setStatus' => \Magento\Framework\Mview\View\StateInterface::STATUS_IDLE,
                'getStatus' => \Magento\Framework\Mview\View\StateInterface::STATUS_IDLE,
                'lock' => 'unlock',
                'isLocked' => false
            ],
            [
                'setStatus' => \Magento\Framework\Mview\View\StateInterface::STATUS_SUSPENDED,
                'getStatus' => \Magento\Framework\Mview\View\StateInterface::STATUS_SUSPENDED,
                'lock' => 'unlock',
                'isLocked' => false
            ]
        ];
    }

    /**
     * @param string $setStatus
     * @param string $getStatus
     * @param bool $isLocked
     * @dataProvider executeProvider
     */
    public function testSetterAndGetterWithApplicationLock($setStatus, $getStatus, $lock, $isLocked)
    {
        $this->configReaderMock->expects($this->any())->method('get')->willReturn(true);
        $this->lockManagerMock->expects($this->any())->method('isLocked')->willReturn($isLocked);
        $this->lockManagerMock->expects($this->once())->method($lock);
        $this->model->setStatus($setStatus);
        $this->assertEquals($getStatus, $this->model->getStatus());
    }
}
