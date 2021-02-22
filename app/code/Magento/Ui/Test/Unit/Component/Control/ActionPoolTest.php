<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Control;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Ui\Component\Control\ActionPool;

class ActionPoolTest extends \PHPUnit\Framework\TestCase
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
     * @var Context| \PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var ItemFactory| \PHPUnit\Framework\MockObject\MockObject
     */
    protected $itemFactoryMock;

    /**
     * @var AbstractBlock| \PHPUnit\Framework\MockObject\MockObject
     */
    protected $toolbarBlockMock;

    /**
     * @var UiComponentInterface| \PHPUnit\Framework\MockObject\MockObject
     */
    protected $uiComponentInterfaceMock;

    /**
     * @var Object[]| \PHPUnit\Framework\MockObject\MockObject
     */
    protected $items;

    /**
     * @var LayoutInterface[]| \PHPUnit\Framework\MockObject\MockObject
     */
    protected $layoutMock;

    /**
     * @var string
     */
    protected $key = 'id';

    protected function setUp(): void
    {
        $this->contextMock = $this->createPartialMock(
            \Magento\Framework\View\Element\UiComponent\Context::class,
            ['getPageLayout']
        );
        $this->toolbarBlockMock = $this->createPartialMock(
            \Magento\Framework\View\Element\AbstractBlock::class,
            ['setChild']
        );
        $this->layoutMock = $this->getMockForAbstractClass(\Magento\Framework\View\LayoutInterface::class);
        $this->contextMock->expects($this->any())->method('getPageLayout')->willReturn($this->layoutMock);
        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with(static::ACTIONS_PAGE_TOOLBAR)
            ->willReturn($this->toolbarBlockMock);

        $this->itemFactoryMock = $this->createPartialMock(\Magento\Ui\Component\Control\ItemFactory::class, ['create']);

        $this->uiComponentInterfaceMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponentInterface::class
        );
        $this->items[$this->key] = $this->createPartialMock(\Magento\Ui\Component\Control\Item::class, ['setData']);
        $this->actionPool = new ActionPool(
            $this->contextMock,
            $this->itemFactoryMock
        );
    }

    public function testAdd()
    {
        $data = ['id' => 'id'];
        $this->uiComponentInterfaceMock->expects($this->once())->method('getName')->willReturn('name');
        $this->itemFactoryMock->expects($this->any())->method('create')->willReturn($this->items[$this->key]);
        $this->items[$this->key]->expects($this->any())->method('setData')->with($data)->willReturnSelf();

        $this->contextMock->expects($this->any())->method('getPageLayout')->willReturn($this->layoutMock);
        $toolbarContainerMock = $this->createMock(\Magento\Backend\Block\Widget\Button\Toolbar\Container::class);
        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->with(
                \Magento\Ui\Component\Control\Container::class,
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
