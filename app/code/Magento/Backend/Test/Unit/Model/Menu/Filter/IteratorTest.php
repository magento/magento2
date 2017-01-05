<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\Menu\Filter;

class IteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Menu
     */
    protected $_menuModel;

    /**
     * @var \Magento\Backend\Model\Menu\Item[]
     */
    protected $_items = [];

    protected function setUp()
    {
        $this->_items['item1'] = $this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false);
        $this->_items['item1']->expects($this->any())->method('getId')->will($this->returnValue('item1'));
        $this->_items['item1']->expects($this->any())->method('isDisabled')->will($this->returnValue(false));
        $this->_items['item1']->expects($this->any())->method('isAllowed')->will($this->returnValue(true));

        $this->_items['item2'] = $this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false);
        $this->_items['item2']->expects($this->any())->method('getId')->will($this->returnValue('item2'));
        $this->_items['item2']->expects($this->any())->method('isDisabled')->will($this->returnValue(true));
        $this->_items['item2']->expects($this->any())->method('isAllowed')->will($this->returnValue(true));

        $this->_items['item3'] = $this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false);
        $this->_items['item3']->expects($this->any())->method('getId')->will($this->returnValue('item3'));
        $this->_items['item3']->expects($this->any())->method('isDisabled')->will($this->returnValue(false));
        $this->_items['item3']->expects($this->any())->method('isAllowed')->will($this->returnValue(false));

        $loggerMock = $this->getMock(\Psr\Log\LoggerInterface::class);

        $this->_menuModel = new \Magento\Backend\Model\Menu($loggerMock);
    }

    public function testLoopWithAllItemsDisabledDoesntIterate()
    {
        $this->_menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->_menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->_menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->_menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->_menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $filterIteratorModel = new \Magento\Backend\Model\Menu\Filter\Iterator(
            $this->_menuModel->getIterator()
        );

        $items = [];
        foreach ($filterIteratorModel as $item) {
            $items[] = $item;
        }
        $this->assertCount(0, $items);
    }

    public function testLoopIteratesOnlyValidItems()
    {
        $this->_menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->_menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));

        $this->_menuModel->add($this->_items['item1']);

        $this->_menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->_menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $filterIteratorModel = new \Magento\Backend\Model\Menu\Filter\Iterator(
            $this->_menuModel->getIterator()
        );

        $items = [];
        foreach ($filterIteratorModel as $item) {
            $items[] = $item;
        }
        $this->assertCount(1, $items);
    }

    public function testLoopIteratesDosntIterateDisabledItems()
    {
        $this->_menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->_menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));

        $this->_menuModel->add($this->_items['item1']);
        $this->_menuModel->add($this->_items['item2']);

        $this->_menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->_menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $filterIteratorModel = new \Magento\Backend\Model\Menu\Filter\Iterator(
            $this->_menuModel->getIterator()
        );

        $items = [];
        foreach ($filterIteratorModel as $item) {
            $items[] = $item;
        }
        $this->assertCount(1, $items);
    }

    public function testLoopIteratesDosntIterateNotAllowedItems()
    {
        $this->_menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->_menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));

        $this->_menuModel->add($this->_items['item1']);
        $this->_menuModel->add($this->_items['item3']);

        $this->_menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->_menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $filterIteratorModel = new \Magento\Backend\Model\Menu\Filter\Iterator(
            $this->_menuModel->getIterator()
        );

        $items = [];
        foreach ($filterIteratorModel as $item) {
            $items[] = $item;
        }
        $this->assertCount(1, $items);
    }

    public function testLoopIteratesMixedItems()
    {
        $this->_menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->_menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));

        $this->_menuModel->add($this->_items['item1']);
        $this->_menuModel->add($this->_items['item2']);
        $this->_menuModel->add($this->_items['item3']);

        $this->_menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $this->_menuModel->add($this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false));
        $filterIteratorModel = new \Magento\Backend\Model\Menu\Filter\Iterator(
            $this->_menuModel->getIterator()
        );

        $items = [];
        foreach ($filterIteratorModel as $item) {
            $items[] = $item;
        }
        $this->assertCount(1, $items);
    }
}
