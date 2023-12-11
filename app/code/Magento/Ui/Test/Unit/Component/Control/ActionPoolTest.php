<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component\Control;

use Magento\Backend\Block\Widget\Button\Toolbar\Container;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Ui\Component\Control\ActionPool;
use Magento\Ui\Component\Control\Item;
use Magento\Ui\Component\Control\ItemFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ActionPoolTest extends TestCase
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
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var ItemFactory|MockObject
     */
    protected $itemFactoryMock;

    /**
     * @var AbstractBlock|MockObject
     */
    protected $toolbarBlockMock;

    /**
     * @var UiComponentInterface|MockObject
     */
    protected $uiComponentInterfaceMock;

    /**
     * @var Object[]|MockObject
     */
    protected $items;

    /**
     * @var LayoutInterface[]|MockObject
     */
    protected $layoutMock;

    /**
     * @var string
     */
    protected $key = 'id';

    protected function setUp(): void
    {
        $this->contextMock = $this->createPartialMock(
            Context::class,
            ['getPageLayout']
        );
        $this->toolbarBlockMock = $this->createPartialMock(
            AbstractBlock::class,
            ['setChild']
        );
        $this->layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->contextMock->expects($this->any())->method('getPageLayout')->willReturn($this->layoutMock);
        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with(static::ACTIONS_PAGE_TOOLBAR)
            ->willReturn($this->toolbarBlockMock);

        $this->itemFactoryMock = $this->createPartialMock(ItemFactory::class, ['create']);

        $this->uiComponentInterfaceMock = $this->getMockForAbstractClass(
            UiComponentInterface::class
        );
        $this->items[$this->key] = $this->createPartialMock(Item::class, ['setData']);
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
        $toolbarContainerMock = $this->createMock(Container::class);
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
