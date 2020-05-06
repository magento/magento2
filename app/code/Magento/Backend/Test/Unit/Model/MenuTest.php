<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model;

use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\Item;
use Magento\Backend\Model\Menu\Item\Factory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class MenuTest extends TestCase
{
    /**
     * @var Menu
     */
    protected $_model;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var Item[]
     */
    protected $_items = [];

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->_items['item1'] = $this->createMock(Item::class);
        $this->_items['item1']->expects($this->any())->method('getId')->willReturn('item1');

        $this->_items['item2'] = $this->createMock(Item::class);
        $this->_items['item2']->expects($this->any())->method('getId')->willReturn('item2');

        $this->_items['item3'] = $this->createMock(Item::class);
        $this->_items['item3']->expects($this->any())->method('getId')->willReturn('item3');

        $this->_logger = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->_model = $this->objectManagerHelper->getObject(
            Menu::class,
            [
                'logger' => $this->_logger
            ]
        );
    }

    public function testAdd()
    {
        $item = $this->createMock(Item::class);
        $this->_model->add($item);
        $this->assertCount(1, $this->_model);
        $this->assertEquals($item, $this->_model[0]);
    }

    public function testAddDoLogAddAction()
    {
        $result = $this->_model->add($this->_items['item1']);
        $this->assertNull($result);
    }

    public function testAddToItem()
    {
        $subMenu = $this->getMockBuilder(Menu::class)
            ->disableOriginalConstructor()
            ->getMock();
        $subMenu->expects($this->once())->method("add")->with($this->_items['item2']);

        $this->_items['item1']->expects($this->once())->method("getChildren")->willReturn($subMenu);

        $this->_model->add($this->_items['item1']);
        $this->_model->add($this->_items['item2'], 'item1');
    }

    public function testAddWithSortIndexThatAlreadyExistsAddsItemOnNextAvailableIndex()
    {
        $this->_model->add($this->createMock(Item::class));
        $this->_model->add($this->createMock(Item::class));
        $this->_model->add($this->createMock(Item::class));

        $this->_model->add($this->_items['item1'], null, 2);
        $this->assertCount(4, $this->_model);
        $this->assertEquals($this->_items['item1'], $this->_model[3]);
    }

    public function testAddSortsItemsByTheirSortIndex()
    {
        $this->_model->add($this->_items['item1'], null, 10);
        $this->_model->add($this->_items['item2'], null, 20);
        $this->_model->add($this->_items['item3'], null, 15);

        $this->assertCount(3, $this->_model);
        $itemsOrdered = [];
        foreach ($this->_model as $item) {
            /** @var \Magento\Backend\Model\Menu\Item $item */
            $itemsOrdered[] = $item->getId();
        }
        $this->assertEquals(['item1', 'item3', 'item2'], $itemsOrdered);
    }

    public function testGet()
    {
        $this->_model->add($this->_items['item1']);
        $this->_model->add($this->_items['item2']);

        $this->assertEquals($this->_items['item1'], $this->_model[0]);
        $this->assertEquals($this->_items['item2'], $this->_model[1]);
    }

    public function testGetRecursive()
    {
        $menuOne = $this->objectManagerHelper->getObject(
            Menu::class,
            [
                'logger' => $this->_logger
            ]
        );
        $menuTwo = $this->objectManagerHelper->getObject(
            Menu::class,
            [
                'logger' => $this->_logger
            ]
        );

        $this->_items['item1']->expects($this->any())->method('hasChildren')->willReturn(true);
        $this->_items['item1']->expects($this->any())->method('getChildren')->willReturn($menuOne);
        $this->_model->add($this->_items['item1']);

        $this->_items['item2']->expects($this->any())->method('hasChildren')->willReturn(true);
        $this->_items['item2']->expects($this->any())->method('getChildren')->willReturn($menuTwo);
        $menuOne->add($this->_items['item2']);

        $this->_items['item3']->expects($this->any())->method('hasChildren')->willReturn(false);
        $menuTwo->add($this->_items['item3']);

        $this->assertEquals($this->_items['item1'], $this->_model->get('item1'));
        $this->assertEquals($this->_items['item2'], $this->_model->get('item2'));
        $this->assertEquals($this->_items['item3'], $this->_model->get('item3'));
    }

    public function testMove()
    {
        $this->_model->add($this->_items['item1']);
        $this->_model->add($this->_items['item2']);
        $this->_model->add($this->_items['item3']);

        $subMenu = $this->getMockBuilder(Menu::class)
            ->disableOriginalConstructor()
            ->getMock();
        $subMenu->expects($this->once())->method("add")->with($this->_items['item3']);

        $this->_items['item1']->expects($this->once())->method("getChildren")->willReturn($subMenu);

        $this->_model->move('item3', 'item1');

        $this->assertCount(2, $this->_model);
        $this->assertArrayNotHasKey(2, $this->_model, "ttt");
    }

    public function testMoveNonExistentItemThrowsException()
    {
        $this->expectException('InvalidArgumentException');
        $this->_model->add($this->_items['item1']);
        $this->_model->add($this->_items['item2']);
        $this->_model->add($this->_items['item3']);

        $this->_model->move('item4', 'item1');
    }

    public function testMoveToNonExistentItemThrowsException()
    {
        $this->expectException('InvalidArgumentException');
        $this->_model->add($this->_items['item1']);
        $this->_model->add($this->_items['item2']);
        $this->_model->add($this->_items['item3']);

        $this->_model->move('item3', 'item4');
    }

    public function testRemoveRemovesMenuItem()
    {
        $this->_model->add($this->_items['item1']);

        $this->assertCount(1, $this->_model);
        $this->assertEquals($this->_items['item1'], $this->_model->get('item1'));

        $this->_model->remove('item1');
        $this->assertCount(0, $this->_model);
        $this->assertNull($this->_model->get('item1'));
    }

    public function testRemoveRemovesMenuItemRecursively()
    {
        $menuMock = $this->getMockBuilder(Menu::class)
            ->disableOriginalConstructor()
            ->getMock();
        $menuMock->expects($this->once())->method('remove')->with('item2');

        $this->_items['item1']->expects($this->any())->method('hasChildren')->willReturn(true);
        $this->_items['item1']->expects($this->any())->method('getChildren')->willReturn($menuMock);
        $this->_model->add($this->_items['item1']);

        $result = $this->_model->remove('item2');
        $this->assertNull($result);
    }

    public function testRemoveDoLogRemoveAction()
    {
        $this->_model->add($this->_items['item1']);
        $result = $this->_model->remove('item1');
        $this->assertTrue($result);
    }

    public function testReorderReordersItemOnTopLevel()
    {
        $this->_model->add($this->_items['item1'], null, 10);
        $this->_model->add($this->_items['item2'], null, 20);

        $this->assertEquals($this->_items['item2'], $this->_model[20]);
        $this->_model->reorder('item2', 5);
        $this->assertEquals($this->_items['item2'], $this->_model[5]);
        $this->assertArrayNotHasKey(20, $this->_model);
    }

    public function testReorderReordersItemOnItsLevel()
    {
        $this->_logger->expects($this->any())->method('log');

        $subMenu = $this->objectManagerHelper->getObject(
            Menu::class,
            [
                'logger' => $this->_logger
            ]
        );

        $this->_items['item1']->expects($this->any())->method("hasChildren")->willReturn(true);

        $this->_items['item1']->expects($this->any())->method("getChildren")->willReturn($subMenu);

        $this->_model->add($this->_items['item1']);
        $this->_model->add($this->_items['item2'], 'item1', 10);
        $this->_model->add($this->_items['item3'], 'item1', 20);

        $this->_model->reorder('item2', 25);
        $subMenu->reorder('item3', 30);

        $this->assertEquals($this->_items['item2'], $subMenu[25]);
        $this->assertEquals($this->_items['item3'], $subMenu[30]);
    }

    public function testIsLast()
    {
        $this->_model->add($this->_items['item1'], null, 10);
        $this->_model->add($this->_items['item2'], null, 16);
        $this->_model->add($this->_items['item3'], null, 15);

        $this->assertTrue($this->_model->isLast($this->_items['item2']));
        $this->assertFalse($this->_model->isLast($this->_items['item3']));
    }

    public function testGetFirstAvailableReturnsLeafNode()
    {
        $item = $this->getMockBuilder(Item::class)
            ->addMethods(['getFirstAvailable'])
            ->onlyMethods(['isAllowed'])
            ->disableOriginalConstructor()
            ->getMock();
        $item->expects($this->never())->method('getFirstAvailable');
        $this->_model->add($item);

        $this->_items['item1']->expects($this->once())->method('isAllowed')->willReturn(true);
        $this->_items['item1']->expects($this->once())->method('isDisabled')->willReturn(false);
        $this->_items['item1']->expects($this->once())->method('hasChildren');
        $this->_model->add($this->_items['item1']);

        $this->assertEquals($this->_items['item1'], $this->_model->getFirstAvailable());
    }

    public function testGetFirstAvailableReturnsOnlyAllowedAndNotDisabledItem()
    {
        $this->_items['item1']->expects($this->exactly(1))->method('isAllowed')->willReturn(true);
        $this->_items['item1']->expects($this->exactly(1))->method('isDisabled')->willReturn(true);
        $this->_model->add($this->_items['item1']);

        $this->_items['item2']->expects($this->exactly(1))->method('isAllowed')->willReturn(false);
        $this->_model->add($this->_items['item2']);

        $this->_items['item3']->expects($this->exactly(1))->method('isAllowed')->willReturn(true);
        $this->_items['item3']->expects($this->exactly(1))->method('isDisabled')->willReturn(false);
        $this->_model->add($this->_items['item3']);

        $this->assertEquals($this->_items['item3'], $this->_model->getFirstAvailable());
    }

    public function testMultipleIterationsWorkProperly()
    {
        $this->_model->add($this->createMock(Item::class));
        $this->_model->add($this->createMock(Item::class));

        $this->_model->add($this->_items['item1']);
        $this->_model->add($this->_items['item2']);

        $items = [];
        /** @var \Magento\Backend\Model\Menu\Item $item */
        foreach ($this->_model as $item) {
            $items[] = $item->getId();
        }

        $itemsTwo = [];
        foreach ($this->_model as $item) {
            $itemsTwo[] = $item->getId();
        }
        $this->assertEquals($items, $itemsTwo);
    }

    /**
     * Test reset iterator to first element before each foreach
     */
    public function testNestedLoop()
    {
        $this->_model->add($this->_items['item1']);
        $this->_model->add($this->_items['item2']);
        $this->_model->add($this->_items['item3']);

        $expected = [
            'item1' => ['item1', 'item2', 'item3'],
            'item2' => ['item1', 'item2', 'item3'],
            'item3' => ['item1', 'item2', 'item3'],
        ];
        $actual = [];
        foreach ($this->_model as $valLoopOne) {
            $keyLevelOne = $valLoopOne->getId();
            foreach ($this->_model as $valLoopTwo) {
                $actual[$keyLevelOne][] = $valLoopTwo->getId();
            }
        }
        $this->assertEquals($expected, $actual);
    }

    public function testSerialize()
    {
        $serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
        $serializerMock->expects($this->once())
            ->method('serialize')
            ->with([['arrayData']])
            ->willReturn('serializedString');
        $menu = $this->objectManagerHelper->getObject(
            Menu::class,
            [
                'logger' => $this->_logger,
                'serializer' => $serializerMock,
            ]
        );
        $itemMock = $this->createMock(Item::class);
        $itemMock->expects($this->any())->method('getId')->willReturn('item1');
        $itemMock->expects($this->once())
            ->method('toArray')
            ->willReturn(['arrayData']);
        $menu->add($itemMock);
        $this->assertEquals('serializedString', $menu->serialize());
    }

    public function testUnserialize()
    {
        $serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
        $serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn([['unserializedData']]);
        $menuItemFactoryMock = $this->createMock(Factory::class);
        $menuItemFactoryMock->expects($this->once())
            ->method('create')
            ->with(['unserializedData']);
        $menu = $this->objectManagerHelper->getObject(
            Menu::class,
            [
                'logger' => $this->_logger,
                'serializer' => $serializerMock,
                'menuItemFactory' => $menuItemFactoryMock,
            ]
        );
        $menu->unserialize('serializedString');
    }
}
