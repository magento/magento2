<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model\Indexer;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Indexer\Model\Indexer\State;
use Magento\Indexer\Model\ResourceModel\Indexer\State\Collection;
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
     * @var \Magento\Indexer\Model\ResourceModel\Indexer\State|MockObject
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
        $this->_resourceMock = $this->createMock(\Magento\Indexer\Model\ResourceModel\Indexer\State::class);
        $this->_resourceCollectionMock = $this->createMock(
            Collection::class
        );
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

    public function testLoadByIndexer()
    {
        $indexerId = 'indexer_id';
        $this->_resourceMock->expects($this->once())->method('load')->with($this->model, $indexerId)->willReturnSelf();
        $this->model->loadByIndexer($indexerId);
        $this->assertEquals($indexerId, $this->model->getIndexerId());
    }

    public function testBeforeSave()
    {
        $this->assertNull($this->model->getUpdated());
        $this->model->beforeSave();
        $this->assertNotNull($this->model->getUpdated());
    }

    public function testSetStatus()
    {
        $setData = 'data';
        $this->model->setStatus($setData);
        $this->assertEquals($setData, $this->model->getStatus());
    }

    public function testSetterAndGetterWithoutApplicationLock()
    {
        $this->configReaderMock->expects($this->any())->method('get')->willReturn(false);

        $this->lockManagerMock->expects($this->any())->method('isLocked')->willReturn(false);

        $status = \Magento\Framework\Indexer\StateInterface::STATUS_WORKING;
        $this->model->setStatus($status);
        $this->assertEquals($status, $this->model->getStatus());

        $date = time();
        $this->model->setUpdated($date);
        $this->assertEquals($date, $this->model->getUpdated());
    }

    /**
     * @return array
     */
    public static function executeProvider()
    {
        return [
            [
                'setStatus' => \Magento\Framework\Indexer\StateInterface::STATUS_WORKING,
                'getStatus' => \Magento\Framework\Indexer\StateInterface::STATUS_WORKING,
                'lock' => 'lock',
                'isLocked' => true
            ],
            [
                'setStatus' => \Magento\Framework\Indexer\StateInterface::STATUS_WORKING,
                'getStatus' => \Magento\Framework\Indexer\StateInterface::STATUS_INVALID,
                'lock' => 'lock',
                'isLocked' => false
            ],
            [
                'setStatus' => \Magento\Framework\Indexer\StateInterface::STATUS_INVALID,
                'getStatus' => \Magento\Framework\Indexer\StateInterface::STATUS_INVALID,
                'lock' => 'unlock',
                'isLocked' => false
            ],
            [
                'setStatus' => \Magento\Framework\Indexer\StateInterface::STATUS_VALID,
                'getStatus' => \Magento\Framework\Indexer\StateInterface::STATUS_VALID,
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
