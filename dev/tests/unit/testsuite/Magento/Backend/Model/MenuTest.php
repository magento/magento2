<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Model;

class MenuTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Menu
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Backend\Model\Menu\Item[]
     */
    protected $_items = array();

    protected function setUp()
    {
        $this->_items['item1'] = $this->getMock('Magento\Backend\Model\Menu\Item', array(), array(), '', false);
        $this->_items['item1']->expects($this->any())->method('getId')->will($this->returnValue('item1'));

        $this->_items['item2'] = $this->getMock('Magento\Backend\Model\Menu\Item', array(), array(), '', false);
        $this->_items['item2']->expects($this->any())->method('getId')->will($this->returnValue('item2'));

        $this->_items['item3'] = $this->getMock('Magento\Backend\Model\Menu\Item', array(), array(), '', false);
        $this->_items['item3']->expects($this->any())->method('getId')->will($this->returnValue('item3'));

        $this->_logger = $this->getMock('Magento\Framework\Logger', array('log'), array(), '', false);

        $this->_model = new \Magento\Backend\Model\Menu($this->_logger);
    }

    public function testAdd()
    {
        $item = $this->getMock('Magento\Backend\Model\Menu\Item', array(), array(), '', false);
        $this->_model->add($item);
        $this->assertCount(1, $this->_model);
        $this->assertEquals($item, $this->_model[0]);
    }

    public function testAddDoLogAddAction()
    {
        $this->_logger->expects(
            $this->once()
        )->method(
            'log'
        )->with(
            $this->equalTo(sprintf('Add of item with id %s was processed', $this->_items['item1']->getId()))
        );

        $this->_model->add($this->_items['item1']);
    }

    public function testAddToItem()
    {
        $subMenu = $this->getMock('Magento\Backend\Model\Menu', array(), array($this->_logger));
        $subMenu->expects($this->once())->method("add")->with($this->_items['item2']);

        $this->_items['item1']->expects($this->once())->method("getChildren")->will($this->returnValue($subMenu));

        $this->_model->add($this->_items['item1']);
        $this->_model->add($this->_items['item2'], 'item1');
    }

    public function testAddWithSortIndexThatAlreadyExistsAddsItemOnNextAvailableIndex()
    {
        $this->_model->add($this->getMock('Magento\Backend\Model\Menu\Item', array(), array(), '', false));
        $this->_model->add($this->getMock('Magento\Backend\Model\Menu\Item', array(), array(), '', false));
        $this->_model->add($this->getMock('Magento\Backend\Model\Menu\Item', array(), array(), '', false));

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
        $itemsOrdered = array();
        foreach ($this->_model as $item) {
            /** @var $item \Magento\Backend\Model\Menu\Item */
            $itemsOrdered[] = $item->getId();
        }
        $this->assertEquals(array('item1', 'item3', 'item2'), $itemsOrdered);
    }

    public function testGet()
    {
        $this->_model->add($this->_items['item1']);
        $this->_model->add($this->_items['item2']);

        $this->assertEquals($this->_items['item1'], $this->_model[0]);
        $this->assertEquals($this->_items['item2'], $this->_model[1]);
        $this->assertEquals($this->_items['item1'], $this->_model->get('item1'));
        $this->assertEquals($this->_items['item2'], $this->_model->get('item2'));
    }

    public function testGetRecursive()
    {
        $menu1 = new \Magento\Backend\Model\Menu($this->_logger);
        $menu2 = new \Magento\Backend\Model\Menu($this->_logger);

        $this->_items['item1']->expects($this->any())->method('hasChildren')->will($this->returnValue(true));
        $this->_items['item1']->expects($this->any())->method('getChildren')->will($this->returnValue($menu1));
        $this->_model->add($this->_items['item1']);

        $this->_items['item2']->expects($this->any())->method('hasChildren')->will($this->returnValue(true));
        $this->_items['item2']->expects($this->any())->method('getChildren')->will($this->returnValue($menu2));
        $menu1->add($this->_items['item2']);

        $this->_items['item3']->expects($this->any())->method('hasChildren')->will($this->returnValue(false));
        $menu2->add($this->_items['item3']);

        $this->assertEquals($this->_items['item1'], $this->_model->get('item1'));
        $this->assertEquals($this->_items['item2'], $this->_model->get('item2'));
        $this->assertEquals($this->_items['item3'], $this->_model->get('item3'));
    }

    public function testMove()
    {
        $this->_model->add($this->_items['item1']);
        $this->_model->add($this->_items['item2']);
        $this->_model->add($this->_items['item3']);

        $subMenu = $this->getMock(
            'Magento\Backend\Model\Menu',
            [],
            [$this->getMock('Magento\Framework\Logger', [], [], '', false)]
        );
        $subMenu->expects($this->once())->method("add")->with($this->_items['item3']);

        $this->_items['item1']->expects($this->once())->method("getChildren")->will($this->returnValue($subMenu));

        $this->_model->move('item3', 'item1');

        $this->assertCount(2, $this->_model);
        $this->assertFalse(isset($this->_model[2]), "ttt");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMoveNonExistentItemThrowsException()
    {
        $this->_model->add($this->_items['item1']);
        $this->_model->add($this->_items['item2']);
        $this->_model->add($this->_items['item3']);

        $this->_model->move('item4', 'item1');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMoveToNonExistentItemThrowsException()
    {
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
        $menuMock = $this->getMock(
            'Magento\Backend\Model\Menu',
            [],
            [$this->getMock('Magento\Framework\Logger', [], [], '', false)]
        );
        $menuMock->expects($this->once())->method('remove')->with($this->equalTo('item2'));

        $this->_items['item1']->expects($this->any())->method('hasChildren')->will($this->returnValue(true));
        $this->_items['item1']->expects($this->any())->method('getChildren')->will($this->returnValue($menuMock));
        $this->_model->add($this->_items['item1']);

        $this->_model->remove('item2');
    }

    public function testRemoveDoLogRemoveAction()
    {
        $this->_model->add($this->_items['item1']);

        $this->_logger->expects(
            $this->once()
        )->method(
            'log'
        )->with(
            $this->equalTo(sprintf('Remove on item with id %s was processed', $this->_items['item1']->getId()))
        );

        $this->_model->remove('item1');
    }

    public function testReorderReordersItemOnTopLevel()
    {
        $this->_model->add($this->_items['item1'], null, 10);
        $this->_model->add($this->_items['item2'], null, 20);

        $this->assertEquals($this->_items['item2'], $this->_model[20]);
        $this->_model->reorder('item2', 5);
        $this->assertEquals($this->_items['item2'], $this->_model[5]);
        $this->assertFalse(isset($this->_model[20]));
    }

    public function testReorderReordersItemOnItsLevel()
    {
        $this->_logger->expects($this->any())->method('log');

        $subMenu = new \Magento\Backend\Model\Menu($this->_logger);

        $this->_items['item1']->expects($this->any())->method("hasChildren")->will($this->returnValue(true));

        $this->_items['item1']->expects($this->any())->method("getChildren")->will($this->returnValue($subMenu));

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
        $item = $this->getMock('Magento\Backend\Model\Menu\Item', array(), array(), '', false);
        $item->expects($this->never())->method('getFirstAvailable');
        $this->_model->add($item);

        $this->_items['item1']->expects($this->once())->method('isAllowed')->will($this->returnValue(true));
        $this->_items['item1']->expects($this->once())->method('isDisabled')->will($this->returnValue(false));
        $this->_items['item1']->expects($this->once())->method('hasChildren');
        $this->_model->add($this->_items['item1']);

        $this->assertEquals($this->_items['item1'], $this->_model->getFirstAvailable());
    }

    public function testGetFirstAvailableReturnsOnlyAllowedAndNotDisabledItem()
    {
        $this->_items['item1']->expects($this->exactly(1))->method('isAllowed')->will($this->returnValue(true));
        $this->_items['item1']->expects($this->exactly(1))->method('isDisabled')->will($this->returnValue(true));
        $this->_model->add($this->_items['item1']);

        $this->_items['item2']->expects($this->exactly(1))->method('isAllowed')->will($this->returnValue(false));
        $this->_model->add($this->_items['item2']);

        $this->_items['item3']->expects($this->exactly(1))->method('isAllowed')->will($this->returnValue(true));
        $this->_items['item3']->expects($this->exactly(1))->method('isDisabled')->will($this->returnValue(false));
        $this->_model->add($this->_items['item3']);

        $this->assertEquals($this->_items['item3'], $this->_model->getFirstAvailable());
    }

    public function testMultipleIterationsWorkProperly()
    {
        $this->_model->add($this->getMock('Magento\Backend\Model\Menu\Item', array(), array(), '', false));
        $this->_model->add($this->getMock('Magento\Backend\Model\Menu\Item', array(), array(), '', false));

        $this->_model->add($this->_items['item1']);
        $this->_model->add($this->_items['item2']);

        $items = array();
        /** @var $item \Magento\Backend\Model\Menu\Item */
        foreach ($this->_model as $item) {
            $items[] = $item->getId();
        }

        $items2 = array();
        foreach ($this->_model as $item) {
            $items2[] = $item->getId();
        }
        $this->assertEquals($items, $items2);
    }

    /**
     * Test reset iterator to first element before each foreach
     */
    public function testNestedLoop()
    {
        $this->_model->add($this->_items['item1']);
        $this->_model->add($this->_items['item2']);
        $this->_model->add($this->_items['item3']);

        $expected = array(
            'item1' => array('item1', 'item2', 'item3'),
            'item2' => array('item1', 'item2', 'item3'),
            'item3' => array('item1', 'item2', 'item3')
        );
        $actual = array();
        foreach ($this->_model as $valLoop1) {
            $keyLevel1 = $valLoop1->getId();
            foreach ($this->_model as $valLoop2) {
                $actual[$keyLevel1][] = $valLoop2->getId();
            }
        }
        $this->assertEquals($expected, $actual);
    }

    public function testSerialize()
    {
        $this->assertNotEmpty($this->_model->serialize());
        $this->_logger->expects($this->once())->method('log');
        $this->_model->add($this->_items['item1']);
    }
}
