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

    protected function setUp(): void
    {
        $this->_contextMock = $this->createPartialMock(Context::class, ['getEventDispatcher']);
        $eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->_contextMock->expects($this->any())->method('getEventDispatcher')->willReturn($eventManagerMock);
        $this->_registryMock = $this->createMock(Registry::class);
        $this->_resourceMock =
            $this->createMock(\Magento\Indexer\Model\ResourceModel\Mview\View\State::class);
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
