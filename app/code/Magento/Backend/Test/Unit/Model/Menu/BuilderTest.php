<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Menu;

use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\Builder;
use Magento\Backend\Model\Menu\Builder\Command\Add;
use Magento\Backend\Model\Menu\Builder\Command\Remove;
use Magento\Backend\Model\Menu\Builder\Command\Update;
use Magento\Backend\Model\Menu\Item;
use Magento\Backend\Model\Menu\Item\Factory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    /**
     * @var Builder
     */
    private $model;

    /**
     * @var Menu|MockObject
     */
    private $menuMock;

    /**
     * @var Factory|MockObject
     */
    private $factoryMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->factoryMock = $this->createMock(Factory::class);
        $this->menuMock = $this->getMockBuilder(Menu::class)
            ->addMethods(['addChild'])
            ->onlyMethods(['add'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = (new ObjectManager($this))->getObject(
            Builder::class,
            [
                'menuItemFactory' => $this->factoryMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testProcessCommand(): void
    {
        $command = $this->createMock(Add::class);
        $command->expects($this->any())->method('getId')->willReturn(1);
        $command2 = $this->createMock(Update::class);
        $command2->expects($this->any())->method('getId')->willReturn(1);
        $command->expects($this->once())->method('chain')->with($command2);
        $this->model->processCommand($command);
        $this->model->processCommand($command2);
    }

    /**
     * @return void
     */
    public function testGetResultBuildsTreeStructure(): void
    {
        $item1 = $this->createMock(Item::class);
        $item1->expects($this->once())->method('getChildren')->willReturn($this->menuMock);
        $this->factoryMock->expects($this->any())->method('create')->willReturn($item1);

        $item2 = $this->createMock(Item::class);
        $this->factoryMock
            ->method('create')
            ->willReturn($item2);

        $this->menuMock
            ->method('add')
            ->willReturnCallback(function (...$args) {
                static $index = 0;
                $expectedArgs = [
                    [$this->isInstanceOf(Item::class), null, 2],
                    [$this->isInstanceOf(Item::class), null, 4]
                ];
                $index++;
                if ($args === $expectedArgs[$index - 1]) {
                    return null;
                }
            });

        $this->model->processCommand(
            new Add(
                [
                    'id' => 'item1',
                    'title' => 'Item 1',
                    'module' => 'Magento_Backend',
                    'sortOrder' => 2,
                    'resource' => 'Magento_Backend::item1'
                ]
            )
        );
        $this->model->processCommand(
            new Add(
                [
                    'id' => 'item2',
                    'parent' => 'item1',
                    'title' => 'two',
                    'module' => 'Magento_Backend',
                    'sortOrder' => 4,
                    'resource' => 'Magento_Backend::item2'
                ]
            )
        );

        $this->model->getResult($this->menuMock);
    }

    /**
     * @return void
     */
    public function testGetResultSkipsRemovedItems(): void
    {
        $this->model->processCommand(
            new Add(
                [
                    'id' => 1,
                    'title' => 'Item 1',
                    'module' => 'Magento_Backend',
                    'resource' => 'Magento_Backend::i1'
                ]
            )
        );
        $this->model->processCommand(new Remove(['id' => 1]));

        $this->menuMock->expects($this->never())->method('addChild');

        $this->model->getResult($this->menuMock);
    }

    /**
     * @return void
     */
    public function testGetResultSkipItemsWithInvalidParent(): void
    {
        $this->expectException('OutOfRangeException');
        $item1 = $this->createMock(Item::class);
        $this->factoryMock->expects($this->any())->method('create')->willReturn($item1);

        $this->model->processCommand(
            new Add(
                [
                    'id' => 'item1',
                    'parent' => 'not_exists',
                    'title' => 'Item 1',
                    'module' => 'Magento_Backend',
                    'resource' => 'Magento_Backend::item1'
                ]
            )
        );

        $this->model->getResult($this->menuMock);
    }
}
