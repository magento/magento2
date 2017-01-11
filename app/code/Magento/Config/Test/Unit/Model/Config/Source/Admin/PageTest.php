<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Config\Model\Config\Source\Admin\Page
 */
namespace Magento\Config\Test\Unit\Model\Config\Source\Admin;

class PageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Menu
     */
    protected $_menuModel;

    /**
     * @var \Magento\Backend\Model\Menu
     */
    protected $_menuSubModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryMock;

    /**
     * @var \Magento\Config\Model\Config\Source\Admin\Page
     */
    protected $_model;

    protected function setUp()
    {
        $logger = $this->getMock(\Psr\Log\LoggerInterface::class);
        $this->_menuModel = new \Magento\Backend\Model\Menu($logger);
        $this->_menuSubModel = new \Magento\Backend\Model\Menu($logger);

        $this->_factoryMock = $this->getMock(
            \Magento\Backend\Model\Menu\Filter\IteratorFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $itemOne = $this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false);
        $itemOne->expects($this->any())->method('getId')->will($this->returnValue('item1'));
        $itemOne->expects($this->any())->method('getTitle')->will($this->returnValue('Item 1'));
        $itemOne->expects($this->any())->method('isAllowed')->will($this->returnValue(true));
        $itemOne->expects($this->any())->method('isDisabled')->will($this->returnValue(false));
        $itemOne->expects($this->any())->method('getAction')->will($this->returnValue('adminhtml/item1'));
        $itemOne->expects($this->any())->method('getChildren')->will($this->returnValue($this->_menuSubModel));
        $itemOne->expects($this->any())->method('hasChildren')->will($this->returnValue(true));
        $this->_menuModel->add($itemOne);

        $itemTwo = $this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false);
        $itemTwo->expects($this->any())->method('getId')->will($this->returnValue('item2'));
        $itemTwo->expects($this->any())->method('getTitle')->will($this->returnValue('Item 2'));
        $itemTwo->expects($this->any())->method('isAllowed')->will($this->returnValue(true));
        $itemTwo->expects($this->any())->method('isDisabled')->will($this->returnValue(false));
        $itemTwo->expects($this->any())->method('getAction')->will($this->returnValue('adminhtml/item2'));
        $itemTwo->expects($this->any())->method('hasChildren')->will($this->returnValue(false));
        $this->_menuSubModel->add($itemTwo);

        $menuConfig = $this->getMock(\Magento\Backend\Model\Menu\Config::class, [], [], '', false);
        $menuConfig->expects($this->once())->method('getMenu')->will($this->returnValue($this->_menuModel));

        $this->_model = new \Magento\Config\Model\Config\Source\Admin\Page($this->_factoryMock, $menuConfig);
    }

    public function testToOptionArray()
    {
        $this->_factoryMock->expects(
            $this->at(0)
        )->method(
            'create'
        )->with(
            $this->equalTo(['iterator' => $this->_menuModel->getIterator()])
        )->will(
            $this->returnValue(new \Magento\Backend\Model\Menu\Filter\Iterator($this->_menuModel->getIterator()))
        );

        $this->_factoryMock->expects(
            $this->at(1)
        )->method(
            'create'
        )->with(
            $this->equalTo(['iterator' => $this->_menuSubModel->getIterator()])
        )->will(
            $this->returnValue(new \Magento\Backend\Model\Menu\Filter\Iterator($this->_menuSubModel->getIterator()))
        );

        $nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
        $paddingString = str_repeat($nonEscapableNbspChar, 4);

        $expected = [
            ['label' => 'Item 1', 'value' => 'item1'],
            ['label' => $paddingString . 'Item 2', 'value' => 'item2'],
        ];
        $this->assertEquals($expected, $this->_model->toOptionArray());
    }
}
