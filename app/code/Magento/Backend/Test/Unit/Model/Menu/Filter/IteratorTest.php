<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\Menu\Filter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class IteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Menu
     */
    protected $menuModel;

    /**
     * @var \Magento\Backend\Model\Menu\Item[]
     */
    protected $items = [];

    protected function setUp()
    {
        $this->items['item1'] = $this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false);
        $this->items['item1']->expects($this->any())->method('getId')->will($this->returnValue('item1'));
        $this->items['item1']->expects($this->any())->method('isDisabled')->will($this->returnValue(false));
        $this->items['item1']->expects($this->any())->method('isAllowed')->will($this->returnValue(true));

        $this->items['item2'] = $this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false);
        $this->items['item2']->expects($this->any())->method('getId')->will($this->returnValue('item2'));
        $this->items['item2']->expects($this->any())->method('isDisabled')->will($this->returnValue(true));
        $this->items['item2']->expects($this->any())->method('isAllowed')->will($this->returnValue(true));

        $this->items['item3'] = $this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false);
        $this->items['item3']->expects($this->any())->method('getId')->will($this->returnValue('item3'));
        $this->items['item3']->expects($this->any())->method('isDisabled')->will($this->returnValue(false));
        $this->items['item3']->expects($this->any())->method('isAllowed')->will($this->returnValue(false));

        $this->menuModel = (new ObjectManager($this))->getObject(\Magento\Backend\Model\Menu::class);
    }

    public function testLoopWithAllItemsDisabledDoesntIterate()
    {
        $this->menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
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
        $this->menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));

        $this->menuModel->add($this->items['item1']);

        $this->menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
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
        $this->menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));

        $this->menuModel->add($this->items['item1']);
        $this->menuModel->add($this->items['item2']);

        $this->menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
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
        $this->menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));

        $this->menuModel->add($this->items['item1']);
        $this->menuModel->add($this->items['item3']);

        $this->menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
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
        $this->menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));

        $this->menuModel->add($this->items['item1']);
        $this->menuModel->add($this->items['item2']);
        $this->menuModel->add($this->items['item3']);

        $this->menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
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
