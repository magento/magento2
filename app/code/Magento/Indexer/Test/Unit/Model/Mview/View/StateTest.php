<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Model\Mview\View;

class StateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Indexer\Model\Mview\View\State
     */
    protected $model;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_contextMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_registryMock;

    /**
     * @var \Magento\Indexer\Model\ResourceModel\Mview\View\State|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_resourceMock;

    /**
     * @var \Magento\Indexer\Model\ResourceModel\Mview\View\State\Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_resourceCollectionMock;

    protected function setUp(): void
    {
        $this->_contextMock = $this->createPartialMock(\Magento\Framework\Model\Context::class, ['getEventDispatcher']);
        $eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->_contextMock->expects($this->any())->method('getEventDispatcher')->willReturn($eventManagerMock);
        $this->_registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->_resourceMock =
            $this->createMock(\Magento\Indexer\Model\ResourceModel\Mview\View\State::class);
        $this->_resourceCollectionMock = $this->createMock(
            \Magento\Indexer\Model\ResourceModel\Mview\View\State\Collection::class
        );

        $this->model = new \Magento\Indexer\Model\Mview\View\State(
            $this->_contextMock,
            $this->_registryMock,
            $this->_resourceMock,
            $this->_resourceCollectionMock
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
        $this->assertTrue(($this->model->getUpdated() != null));
    }

    public function testSetterAndGetter()
    {
        $setData = 'data';
        $this->model->setMode($setData);
        $this->assertEquals($setData, $this->model->getMode());
        $this->model->setStatus($setData);
        $this->assertEquals($setData, $this->model->getStatus());
        $this->model->setUpdated($setData);
        $this->assertEquals($setData, $this->model->getUpdated());
        $this->model->setVersionId($setData);
        $this->assertEquals($setData, $this->model->getVersionId());
    }
}
