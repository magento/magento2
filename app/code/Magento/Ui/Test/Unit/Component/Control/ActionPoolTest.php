<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Control;

use \Magento\Ui\Component\Control\ActionPool;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\LayoutInterface;

class ActionPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Actions toolbar block name
     */
    const ACTIONS_PAGE_TOOLBAR = 'page.actions.toolbar';

    /**
     * @var ActionPool
     */
    protected $actionPool;

    /**
     * @var Context| \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var ItemFactory| \PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemFactoryMock;

    /**
     * @var AbstractBlock| \PHPUnit_Framework_MockObject_MockObject
     */
    protected $toolbarBlockMock;

    /**
     * @var UiComponentInterface| \PHPUnit_Framework_MockObject_MockObject
     */
    protected $uiComponentInterfaceMock;

    /**
     * @var Object[]| \PHPUnit_Framework_MockObject_MockObject
     */
    protected $items;

    /**
     * @var LayoutInterface[]| \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var string
     */
    protected $key = 'id';

    protected function setUp()
    {
        $this->contextMock = $this->getMock(
            'Magento\Framework\View\Element\UiComponent\Context',
            ['getPageLayout'],
            [],
            '',
            false
        );
        $this->toolbarBlockMock = $this->getMock(
            'Magento\Framework\View\Element\AbstractBlock',
            ['setChild'],
            [],
            '',
            false
        );
        $this->layoutMock = $this->getMockForAbstractClass('Magento\Framework\View\LayoutInterface');
        $this->contextMock->expects($this->any())->method('getPageLayout')->willReturn($this->layoutMock);
        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with(static::ACTIONS_PAGE_TOOLBAR)
            ->willReturn($this->toolbarBlockMock);

        $this->itemFactoryMock = $this->getMock('Magento\Ui\Component\Control\ItemFactory', ['create'], [], '', false);

        $this->uiComponentInterfaceMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponentInterface'
        );
        $this->items[$this->key] = $this->getMock('Magento\Ui\Component\Control\Item', ['setData'], [], '', false);
        $this->actionPool = new ActionPool(
            $this->contextMock,
            $this->itemFactoryMock,
            $this->toolbarBlockMock
        );
    }

    public function testAdd()
    {
        $data = ['id' => 'id'];
        $this->uiComponentInterfaceMock->expects($this->once())->method('getName')->willReturn('name');
        $this->itemFactoryMock->expects($this->any())->method('create')->willReturn($this->items[$this->key]);
        $this->items[$this->key]->expects($this->any())->method('setData')->with($data)->willReturnSelf();

        $this->contextMock->expects($this->any())->method('getPageLayout')->willReturn($this->layoutMock);
        $toolbarContainerMock = $this->getMock(
            'Magento\Backend\Block\Widget\Button\Toolbar\Container',
            [],
            [],
            '',
            false
        );
        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->with(
                'Magento\Ui\Component\Control\Container',
                'container-name-' . $this->key,
                [
                    'data' => [
                        'button_item' => $this->items[$this->key],
                        'context' => $this->uiComponentInterfaceMock,
                    ]
                ]
            )
            ->willReturn($toolbarContainerMock);
        $this->toolbarBlockMock->expects($this->once())
            ->method('setChild')
            ->with($this->key, $toolbarContainerMock)
            ->willReturnSelf();
        $this->actionPool->add($this->key, $data, $this->uiComponentInterfaceMock);
    }

    public function testRemove()
    {
        $this->testAdd();
        $this->actionPool->remove($this->key);
    }

    public function testUpdate()
    {
        $this->testAdd();
        $data = ['id' => 'id'];
        $this->items[$this->key]->expects($this->any())->method('setData')->with($data)->willReturnSelf();
        $this->actionPool->update($this->key, $data);
    }
}
