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
 * @category    Magento
 * @package     Mage_Backend
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Backend_Block_Widget_Grid_ColumnSetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Block_Widget_Grid_ColumnSet
     */
    protected $_block;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_columnMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_subtotalsMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_totalsMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_gridMock;

    protected function setUp()
    {
        $this->_columnMock = $this->getMock('Mage_Backend_Block_Widget_Grid_Column',
            array('setSortable', 'setRendererType', 'setFilterType'), array(), '', false);
        $this->_layoutMock = $this->getMock('Mage_Core_Model_Layout', array(), array(), '', false);
        $this->_layoutMock
            ->expects($this->any())
            ->method('getChildBlocks')
            ->will($this->returnValue(array('column' => $this->_columnMock)));
        $this->_helperMock = $this->getMock('Mage_Backend_Helper_Data', array(), array(), '', false);
        $this->_helperMock
            ->expects($this->any())
            ->method('__')
            ->will($this->returnValue('TRANSLATED STRING'));
        $this->_factoryMock = $this->getMock('Mage_Backend_Model_Widget_Grid_Row_UrlGeneratorFactory', array(), array(),
            '', false
        );

        $this->_subtotalsMock = $this->getMock(
            'Mage_Backend_Model_Widget_Grid_SubTotals', array(), array(), '', false
        );

        $this->_totalsMock = $this->getMock(
            'Mage_Backend_Model_Widget_Grid_Totals', array(), array(), '', false
        );

        $arguments = array(
            'layout'           => $this->_layoutMock,
            'helper'           => $this->_helperMock,
            'generatorFactory' => $this->_factoryMock,
            'totals' => $this->_totalsMock,
            'subtotals' => $this->_subtotalsMock
        );

        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        $this->_block = $objectManagerHelper->getBlock('Mage_Backend_Block_Widget_Grid_ColumnSet', $arguments);
        $this->_block->setNameInLayout('grid.columnSet');

    }

    public function tearDown()
    {
        unset($this->_block);
        unset($this->_layoutMock);
        unset($this->_columnMock);
        unset($this->_helperMock);
        unset($this->_factoryMock);
        unset($this->_totalsMock);
        unset($this->_subtotalsMock);
    }

    public function testSetSortablePropagatesSortabilityToChildren()
    {
        $this->_columnMock->expects($this->once())->method('setSortable')->with(false);
        $this->_block->setSortable(false);
    }

    public function testSetSortablePropagatesSortabilityToChildrenOnlyIfSortabilityIsFalse()
    {
        $this->_columnMock->expects($this->never())->method('setSortable');
        $this->_block->setSortable(true);
    }

    public function testSetRendererTypePropagatesRendererTypeToColumns()
    {
        $this->_columnMock->expects($this->once())->method('setRendererType')->with('renderer', 'Renderer_Class');
        $this->_block->setRendererType('renderer', 'Renderer_Class');
    }

    public function testSetFilterTypePropagatesFilterTypeToColumns()
    {
        $this->_columnMock->expects($this->once())->method('setFilterType')->with('filter', 'Filter_Class');
        $this->_block->setFilterType('filter', 'Filter_Class');
    }

    public function testGetRowUrlIfUrlPathNotSet()
    {
        $this->assertEquals('#', $this->_block->getRowUrl(new StdClass()));
    }

    public function testGetRowUrl()
    {
        $generatorClass = 'Mage_Backend_Model_Widget_Grid_Row_UrlGenerator';

        $itemMock = $this->getMock('Varien_Object', array(), array(), '', false);

        $rowUrlGenerator = $this->getMock('Mage_Backend_Model_Widget_Grid_Row_UrlGenerator', array('getUrl'), array(),
            '', false
        );
        $rowUrlGenerator->expects($this->once())
            ->method('getUrl')
            ->with($this->equalTo($itemMock))
            ->will($this->returnValue('http://localhost/mng/item/edit'));

        $factoryMock = $this->getMock('Mage_Backend_Model_Widget_Grid_Row_UrlGeneratorFactory',
            array('createUrlGenerator'), array(), '', false
        );
        $factoryMock->expects($this->once())
            ->method('createUrlGenerator')
            ->with($this->equalTo($generatorClass),
            $this->equalTo(array('args' => array('generatorClass' => $generatorClass)))
        )
            ->will($this->returnValue($rowUrlGenerator));

        $arguments = array(
            'layout'           => $this->_layoutMock,
            'helper'           => $this->_helperMock,
            'generatorFactory' => $factoryMock,
            'data'             => array(
                'rowUrl' => array('generatorClass' => $generatorClass)
            ),
            'totals' => $this->_totalsMock,
            'subtotals' => $this->_subtotalsMock
        );

        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        /** @var $model Mage_Backend_Block_Widget_Grid_ColumnSet */
        $model = $objectManagerHelper->getBlock('Mage_Backend_Block_Widget_Grid_ColumnSet', $arguments);

        $url = $model->getRowUrl($itemMock);
        $this->assertEquals('http://localhost/mng/item/edit', $url);
    }

    public function testItemHasMultipleRows()
    {
        $item =  new Varien_Object();
        // prepare sub-collection
        $subCollection = new Varien_Data_Collection();
        $subCollection->addItem(new Varien_Object(array('test4' => '1','test5' => '2')));
        $subCollection->addItem(new Varien_Object(array('test4' => '2','test5' => '2')));
        $item->setChildren($subCollection);

        $this->assertTrue($this->_block->hasMultipleRows($item));
    }

    public function testShouldRenderTotalWithNotEmptyCollection()
    {
        $this->_prepareLayoutWithGrid($this->_prepareGridMock($this->_getTestCollection()));

        $this->_block->setCountTotals(true);
        $this->assertTrue($this->_block->shouldRenderTotal());
    }

    public function testShouldRenderTotalWithEmptyCollection()
    {
        $this->_prepareLayoutWithGrid($this->_prepareGridMock(new Varien_Data_Collection()));

        $this->_block->setCountTotals(true);
        $this->assertFalse($this->_block->shouldRenderTotal());
    }

    public function testShouldRenderTotalWithFlagFalse()
    {
        $this->_block->setCountTotals(false);
        $this->assertFalse($this->_block->shouldRenderTotal());
    }

    public function testShouldRenderSubtotalWithFlagFalse()
    {
        $this->_block->setCountSubTotals(false);
        $this->assertFalse($this->_block->shouldRenderSubTotal(new Varien_Object()));
    }

    public function testShouldRenderSubtotalWithEmptySubData()
    {
        $this->_block->setCountSubTotals(true);
        $this->assertFalse($this->_block->shouldRenderSubTotal(new Varien_Object()));
    }

    public function testShouldRenderSubtotalWithNotEmptySubData()
    {
        $item =  new Varien_Object();
        // prepare sub-collection
        $subCollection = new Varien_Data_Collection();
        $subCollection->addItem(new Varien_Object(array('test4' => '1','test5' => '2')));
        $subCollection->addItem(new Varien_Object(array('test4' => '2','test5' => '2')));
        $item->setChildren($subCollection);

        $this->_block->setCountSubTotals(true);
        $this->assertTrue($this->_block->shouldRenderSubTotal($item));
    }

    public function testUpdateItemByFirstMultiRow()
    {
        $item =  new Varien_Object(array('test1' => '1'));
        // prepare sub-collection
        $subCollection = new Varien_Data_Collection();
        $subCollection->addItem(new Varien_Object(array('test4' => '1','test5' => '2')));
        $subCollection->addItem(new Varien_Object(array('test4' => '2','test5' => '2')));
        $item->setChildren($subCollection);

        $expectedItem = new Varien_Object(array('test1' => '1'));
        $expectedItem->addData(array('test4' => '1','test5' => '2'));
        $expectedItem->setChildren($subCollection);

        $this->_block->updateItemByFirstMultiRow($item);
        $this->assertEquals($expectedItem, $item);
    }

    public function testGetSubTotals()
    {
        // prepare sub-collection
        $subCollection = new Varien_Data_Collection();
        $subCollection->addItem(new Varien_Object(array('column' => '1')));
        $subCollection->addItem(new Varien_Object(array('column' => '1')));

        $this->_subtotalsMock->expects($this->once())
            ->method('countTotals')
            ->with($subCollection)
            ->will($this->returnValue(new Varien_Object(array('column' => '2'))));

        // prepare item
        $item =  new Varien_Object(array('test1' => '1'));
        $item->setChildren($subCollection);

        $this->assertEquals(
            new Varien_Object(array('column' => '2')),
            $this->_block->getSubTotals($item)
        );
    }

    public function testGetTotals()
    {
        $collection = $this->_getTestCollection();
        $this->_prepareLayoutWithGrid($this->_prepareGridMock($collection));

        $this->_totalsMock->expects($this->once())
            ->method('countTotals')
            ->with($collection)
            ->will($this->returnValue(new Varien_Object(array('test1' => '3', 'test2' => '2'))));

        $this->assertEquals(
            new Varien_Object(array('test1' => '3', 'test2' => '2')),
            $this->_block->getTotals()
        );
    }

    /**
     * Retrieve prepared mock for Mage_Backend_Model_Widget_Grid with collection
     *
     * @param Varien_Data_Collection $collection
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _prepareGridMock($collection)
    {
        // prepare block grid
        $gridMock = $this->getMock('Mage_Backend_Model_Widget_Grid', array('getCollection'), array(), '', true);
        $gridMock->expects($this->any())
            ->method('getCollection')
            ->will($this->returnValue($collection));

        return $gridMock;
    }

    /**
     * Retrieve test collection
     *
     * @return Varien_Data_Collection
     */
    protected function _getTestCollection()
    {
        $collection = new Varien_Data_Collection();
        $items = array(
            new Varien_Object(array('test1' => '1', 'test2' => '2')),
            new Varien_Object(array('test1' => '1', 'test2' => '2')),
            new Varien_Object(array('test1' => '1', 'test2' => '2'))
        );
        foreach ($items as $item) {
            $collection->addItem($item);
        }

        return $collection;
    }

    /**
     * Prepare layout for receiving grid block
     *
     * @param PHPUnit_Framework_MockObject_MockObject $gridMock
     */
    protected function _prepareLayoutWithGrid($gridMock)
    {
        $this->_layoutMock->expects($this->any())
            ->method('getParentName')
            ->with('grid.columnSet')
            ->will($this->returnValue('grid'));
        $this->_layoutMock->expects($this->any())
            ->method('getBlock')
            ->with('grid')
            ->will($this->returnValue($gridMock));
    }
}
