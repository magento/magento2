<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Model\Indexer;

class StateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Indexer\Model\Indexer\State
     */
    protected $model;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_contextMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_registryMock;

    /**
     * @var \Magento\Indexer\Model\ResourceModel\Indexer\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceMock;

    /**
     * @var \Magento\Indexer\Model\ResourceModel\Indexer\State\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceCollectionMock;

    protected function setUp()
    {
        $this->_contextMock = $this->createPartialMock(\Magento\Framework\Model\Context::class, ['getEventDispatcher']);
        $eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->_contextMock->expects($this->any())->method('getEventDispatcher')->willReturn($eventManagerMock);
        $this->_registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->_resourceMock = $this->createMock(\Magento\Indexer\Model\ResourceModel\Indexer\State::class);
        $this->_resourceCollectionMock = $this->createMock(
            \Magento\Indexer\Model\ResourceModel\Indexer\State\Collection::class
        );

        $this->model = new \Magento\Indexer\Model\Indexer\State(
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
