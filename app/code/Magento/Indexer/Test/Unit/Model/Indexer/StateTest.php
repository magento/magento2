<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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

    protected function setUp(): void
    {
        $this->_contextMock = $this->createPartialMock(Context::class, ['getEventDispatcher']);
        $eventManagerMock = $this->createMock(ManagerInterface::class);
        $this->_contextMock->expects($this->any())->method('getEventDispatcher')->willReturn($eventManagerMock);
        $this->_registryMock = $this->createMock(Registry::class);
        $this->_resourceMock = $this->createMock(\Magento\Indexer\Model\ResourceModel\Indexer\State::class);
        $this->_resourceCollectionMock = $this->createMock(
            Collection::class
        );

        $this->model = new State(
            $this->_contextMock,
            $this->_registryMock,
            $this->_resourceMock,
            $this->_resourceCollectionMock
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
        $this->assertEquals(null, $this->model->getUpdated());
        $this->model->beforeSave();
        $this->assertTrue(($this->model->getUpdated() != null));
    }

    public function testSetStatus()
    {
        $setData = 'data';
        $this->model->setStatus($setData);
        $this->assertEquals($setData, $this->model->getStatus());
    }
}
