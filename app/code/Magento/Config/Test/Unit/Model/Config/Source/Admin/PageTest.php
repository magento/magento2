<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Config\Model\Config\Source\Admin\Page
 */
namespace Magento\Config\Test\Unit\Model\Config\Source\Admin;

class PageTest extends \PHPUnit\Framework\TestCase
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
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_factoryMock;

    /**
     * @var \Magento\Config\Model\Config\Source\Admin\Page
     */
    protected $_model;

    protected function setUp(): void
    {
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->_menuModel = new \Magento\Backend\Model\Menu($logger);
        $this->_menuSubModel = new \Magento\Backend\Model\Menu($logger);

        $this->_factoryMock = $this->createPartialMock(
            \Magento\Backend\Model\Menu\Filter\IteratorFactory::class,
            ['create']
        );

        $itemOne = $this->createMock(\Magento\Backend\Model\Menu\Item::class);
        $itemOne->expects($this->any())->method('getId')->willReturn('item1');
        $itemOne->expects($this->any())->method('getTitle')->willReturn('Item 1');
        $itemOne->expects($this->any())->method('isAllowed')->willReturn(true);
        $itemOne->expects($this->any())->method('isDisabled')->willReturn(false);
        $itemOne->expects($this->any())->method('getAction')->willReturn('adminhtml/item1');
        $itemOne->expects($this->any())->method('getChildren')->willReturn($this->_menuSubModel);
        $itemOne->expects($this->any())->method('hasChildren')->willReturn(true);
        $this->_menuModel->add($itemOne);

        $itemTwo = $this->createMock(\Magento\Backend\Model\Menu\Item::class);
        $itemTwo->expects($this->any())->method('getId')->willReturn('item2');
        $itemTwo->expects($this->any())->method('getTitle')->willReturn('Item 2');
        $itemTwo->expects($this->any())->method('isAllowed')->willReturn(true);
        $itemTwo->expects($this->any())->method('isDisabled')->willReturn(false);
        $itemTwo->expects($this->any())->method('getAction')->willReturn('adminhtml/item2');
        $itemTwo->expects($this->any())->method('hasChildren')->willReturn(false);
        $this->_menuSubModel->add($itemTwo);

        $menuConfig = $this->createMock(\Magento\Backend\Model\Menu\Config::class);
        $menuConfig->expects($this->once())->method('getMenu')->willReturn($this->_menuModel);

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
        )->willReturn(
            new \Magento\Backend\Model\Menu\Filter\Iterator($this->_menuModel->getIterator())
        );

        $this->_factoryMock->expects(
            $this->at(1)
        )->method(
            'create'
        )->with(
            $this->equalTo(['iterator' => $this->_menuSubModel->getIterator()])
        )->willReturn(
            new \Magento\Backend\Model\Menu\Filter\Iterator($this->_menuSubModel->getIterator())
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
