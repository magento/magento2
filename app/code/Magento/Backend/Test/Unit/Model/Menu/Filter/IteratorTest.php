<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\Menu\Filter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class IteratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Model\Menu
     */
    private $menuModel;

    /**
     * @var \Magento\Backend\Model\Menu\Item[]
     */
    private $items = [];

    protected function setUp(): void
    {
        $this->items['item1'] = $this->createMock(\Magento\Backend\Model\Menu\Item::class);
        $this->items['item1']->expects($this->any())->method('getId')->willReturn('item1');
        $this->items['item1']->expects($this->any())->method('isDisabled')->willReturn(false);
        $this->items['item1']->expects($this->any())->method('isAllowed')->willReturn(true);

        $this->items['item2'] = $this->createMock(\Magento\Backend\Model\Menu\Item::class);
        $this->items['item2']->expects($this->any())->method('getId')->willReturn('item2');
        $this->items['item2']->expects($this->any())->method('isDisabled')->willReturn(true);
        $this->items['item2']->expects($this->any())->method('isAllowed')->willReturn(true);

        $this->items['item3'] = $this->createMock(\Magento\Backend\Model\Menu\Item::class);
        $this->items['item3']->expects($this->any())->method('getId')->willReturn('item3');
        $this->items['item3']->expects($this->any())->method('isDisabled')->willReturn(false);
        $this->items['item3']->expects($this->any())->method('isAllowed')->willReturn(false);

        $this->menuModel = (new ObjectManager($this))->getObject(\Magento\Backend\Model\Menu::class);
    }

    public function testLoopWithAllItemsDisabledDoesntIterate()
    {
        $this->menuModel->add($this->createMock(\Magento\Backend\Model\Menu\Item::class));
        $this->menuModel->add($this->createMock(\Magento\Backend\Model\Menu\Item::class));
        $this->menuModel->add($this->createMock(\Magento\Backend\Model\Menu\Item::class));
        $this->menuModel->add($this->createMock(\Magento\Backend\Model\Menu\Item::class));
        $this->menuModel->add($this->createMock(\Magento\Backend\Model\Menu\Item::class));
        $filterIteratorModel = new \Magento\Backend\Model\Menu\Filter\Iterator(
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
        $this->menuModel->add($this->createMock(\Magento\Backend\Model\Menu\Item::class));
        $this->menuModel->add($this->createMock(\Magento\Backend\Model\Menu\Item::class));

        $this->menuModel->add($this->items['item1']);

        $this->menuModel->add($this->createMock(\Magento\Backend\Model\Menu\Item::class));
        $this->menuModel->add($this->createMock(\Magento\Backend\Model\Menu\Item::class));
        $filterIteratorModel = new \Magento\Backend\Model\Menu\Filter\Iterator(
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
        $this->menuModel->add($this->createMock(\Magento\Backend\Model\Menu\Item::class));
        $this->menuModel->add($this->createMock(\Magento\Backend\Model\Menu\Item::class));

        $this->menuModel->add($this->items['item1']);
        $this->menuModel->add($this->items['item2']);

        $this->menuModel->add($this->createMock(\Magento\Backend\Model\Menu\Item::class));
        $this->menuModel->add($this->createMock(\Magento\Backend\Model\Menu\Item::class));
        $filterIteratorModel = new \Magento\Backend\Model\Menu\Filter\Iterator(
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
        $this->menuModel->add($this->createMock(\Magento\Backend\Model\Menu\Item::class));
        $this->menuModel->add($this->createMock(\Magento\Backend\Model\Menu\Item::class));

        $this->menuModel->add($this->items['item1']);
        $this->menuModel->add($this->items['item3']);

        $this->menuModel->add($this->createMock(\Magento\Backend\Model\Menu\Item::class));
        $this->menuModel->add($this->createMock(\Magento\Backend\Model\Menu\Item::class));
        $filterIteratorModel = new \Magento\Backend\Model\Menu\Filter\Iterator(
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
        $this->menuModel->add($this->createMock(\Magento\Backend\Model\Menu\Item::class));
        $this->menuModel->add($this->createMock(\Magento\Backend\Model\Menu\Item::class));

        $this->menuModel->add($this->items['item1']);
        $this->menuModel->add($this->items['item2']);
        $this->menuModel->add($this->items['item3']);

        $this->menuModel->add($this->createMock(\Magento\Backend\Model\Menu\Item::class));
        $this->menuModel->add($this->createMock(\Magento\Backend\Model\Menu\Item::class));
        $filterIteratorModel = new \Magento\Backend\Model\Menu\Filter\Iterator(
            $this->menuModel->getIterator()
        );

        $items = [];
        foreach ($filterIteratorModel as $item) {
            $items[] = $item;
        }
        $this->assertCount(1, $items);
    }
}
