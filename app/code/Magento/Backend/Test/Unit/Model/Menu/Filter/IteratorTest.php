<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Menu\Filter;

use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\Filter\Iterator;
use Magento\Backend\Model\Menu\Item;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class IteratorTest extends TestCase
{
    /**
     * @var Menu
     */
    private $menuModel;

    /**
     * @var Item[]
     */
    private $items = [];

    protected function setUp(): void
    {
        $this->items['item1'] = $this->createMock(Item::class);
        $this->items['item1']->expects($this->any())->method('getId')->willReturn('item1');
        $this->items['item1']->expects($this->any())->method('isDisabled')->willReturn(false);
        $this->items['item1']->expects($this->any())->method('isAllowed')->willReturn(true);

        $this->items['item2'] = $this->createMock(Item::class);
        $this->items['item2']->expects($this->any())->method('getId')->willReturn('item2');
        $this->items['item2']->expects($this->any())->method('isDisabled')->willReturn(true);
        $this->items['item2']->expects($this->any())->method('isAllowed')->willReturn(true);

        $this->items['item3'] = $this->createMock(Item::class);
        $this->items['item3']->expects($this->any())->method('getId')->willReturn('item3');
        $this->items['item3']->expects($this->any())->method('isDisabled')->willReturn(false);
        $this->items['item3']->expects($this->any())->method('isAllowed')->willReturn(false);

        $this->menuModel = (new ObjectManager($this))->getObject(Menu::class);
    }

    public function testLoopWithAllItemsDisabledDoesntIterate()
    {
        $this->menuModel->add($this->createMock(Item::class));
        $this->menuModel->add($this->createMock(Item::class));
        $this->menuModel->add($this->createMock(Item::class));
        $this->menuModel->add($this->createMock(Item::class));
        $this->menuModel->add($this->createMock(Item::class));
        $filterIteratorModel = new Iterator(
            $this->menuModel->getIterator()
        );

        $items = [];
        foreach ($filterIteratorModel as $item) {
            $items[] = $item;
        }
        $this->assertCount(0, $items);
    }

    public function testLoopIteratesOnlyValidItems()
    {
        $this->menuModel->add($this->createMock(Item::class));
        $this->menuModel->add($this->createMock(Item::class));

        $this->menuModel->add($this->items['item1']);

        $this->menuModel->add($this->createMock(Item::class));
        $this->menuModel->add($this->createMock(Item::class));
        $filterIteratorModel = new Iterator(
            $this->menuModel->getIterator()
        );

        $items = [];
        foreach ($filterIteratorModel as $item) {
            $items[] = $item;
        }
        $this->assertCount(1, $items);
    }

    public function testLoopIteratesDosntIterateDisabledItems()
    {
        $this->menuModel->add($this->createMock(Item::class));
        $this->menuModel->add($this->createMock(Item::class));

        $this->menuModel->add($this->items['item1']);
        $this->menuModel->add($this->items['item2']);

        $this->menuModel->add($this->createMock(Item::class));
        $this->menuModel->add($this->createMock(Item::class));
        $filterIteratorModel = new Iterator(
            $this->menuModel->getIterator()
        );

        $items = [];
        foreach ($filterIteratorModel as $item) {
            $items[] = $item;
        }
        $this->assertCount(1, $items);
    }

    public function testLoopIteratesDosntIterateNotAllowedItems()
    {
        $this->menuModel->add($this->createMock(Item::class));
        $this->menuModel->add($this->createMock(Item::class));

        $this->menuModel->add($this->items['item1']);
        $this->menuModel->add($this->items['item3']);

        $this->menuModel->add($this->createMock(Item::class));
        $this->menuModel->add($this->createMock(Item::class));
        $filterIteratorModel = new Iterator(
            $this->menuModel->getIterator()
        );

        $items = [];
        foreach ($filterIteratorModel as $item) {
            $items[] = $item;
        }
        $this->assertCount(1, $items);
    }

    public function testLoopIteratesMixedItems()
    {
        $this->menuModel->add($this->createMock(Item::class));
        $this->menuModel->add($this->createMock(Item::class));

        $this->menuModel->add($this->items['item1']);
        $this->menuModel->add($this->items['item2']);
        $this->menuModel->add($this->items['item3']);

        $this->menuModel->add($this->createMock(Item::class));
        $this->menuModel->add($this->createMock(Item::class));
        $filterIteratorModel = new Iterator(
            $this->menuModel->getIterator()
        );

        $items = [];
        foreach ($filterIteratorModel as $item) {
            $items[] = $item;
        }
        $this->assertCount(1, $items);
    }
}
