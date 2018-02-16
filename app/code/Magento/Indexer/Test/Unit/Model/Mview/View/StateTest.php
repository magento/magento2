<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Model\Mview\View;

class StateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\Model\Mview\View\State
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
     * @var \Magento\Indexer\Model\ResourceModel\Mview\View\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceMock;

    /**
     * @var \Magento\Indexer\Model\ResourceModel\Mview\View\State\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceCollectionMock;

    protected function setUp()
    {
        $this->_contextMock = $this->getMock('\Magento\Framework\Model\Context', ['getEventDispatcher'], [], '', false);
        $eventManagerMock = $this->getMock('\Magento\Framework\Event\ManagerInterface');
        $this->_contextMock->expects($this->any())->method('getEventDispatcher')->willReturn($eventManagerMock);
        $this->_registryMock = $this->getMock('\Magento\Framework\Registry', [], [], '', false);
        $this->_resourceMock =
            $this->getMock('\Magento\Indexer\Model\ResourceModel\Mview\View\State', [], [], '', false);
        $this->_resourceCollectionMock = $this->getMock(
            '\Magento\Indexer\Model\ResourceModel\Mview\View\State\Collection',
            [],
            [],
            '',
            false
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
        $this->assertEquals(null, $this->model->getUpdated());
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
