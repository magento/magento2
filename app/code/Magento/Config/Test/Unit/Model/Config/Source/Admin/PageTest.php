<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Config\Model\Config\Source\Admin\Page
 */
namespace Magento\Config\Test\Unit\Model\Config\Source\Admin;

use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\Config;
use Magento\Backend\Model\Menu\Filter\Iterator;
use Magento\Backend\Model\Menu\Filter\IteratorFactory;
use Magento\Backend\Model\Menu\Item;
use Magento\Config\Model\Config\Source\Admin\Page;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PageTest extends TestCase
{
    /**
     * @var Menu
     */
    protected $_menuModel;

    /**
     * @var Menu
     */
    protected $_menuSubModel;

    /**
     * @var MockObject
     */
    protected $_factoryMock;

    /**
     * @var Page
     */
    protected $_model;

    protected function setUp(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $this->_menuModel = new Menu($logger);
        $this->_menuSubModel = new Menu($logger);

        $this->_factoryMock = $this->createPartialMock(
            IteratorFactory::class,
            ['create']
        );

        $itemOne = $this->createMock(Item::class);
        $itemOne->expects($this->any())->method('getId')->will($this->returnValue('item1'));
        $itemOne->expects($this->any())->method('getTitle')->will($this->returnValue('Item 1'));
        $itemOne->expects($this->any())->method('isAllowed')->will($this->returnValue(true));
        $itemOne->expects($this->any())->method('isDisabled')->will($this->returnValue(false));
        $itemOne->expects($this->any())->method('getAction')->will($this->returnValue('adminhtml/item1'));
        $itemOne->expects($this->any())->method('getChildren')->will($this->returnValue($this->_menuSubModel));
        $itemOne->expects($this->any())->method('hasChildren')->will($this->returnValue(true));
        $this->_menuModel->add($itemOne);

        $itemTwo = $this->createMock(Item::class);
        $itemTwo->expects($this->any())->method('getId')->will($this->returnValue('item2'));
        $itemTwo->expects($this->any())->method('getTitle')->will($this->returnValue('Item 2'));
        $itemTwo->expects($this->any())->method('isAllowed')->will($this->returnValue(true));
        $itemTwo->expects($this->any())->method('isDisabled')->will($this->returnValue(false));
        $itemTwo->expects($this->any())->method('getAction')->will($this->returnValue('adminhtml/item2'));
        $itemTwo->expects($this->any())->method('hasChildren')->will($this->returnValue(false));
        $this->_menuSubModel->add($itemTwo);

        $menuConfig = $this->createMock(Config::class);
        $menuConfig->expects($this->once())->method('getMenu')->will($this->returnValue($this->_menuModel));

        $this->_model = new Page($this->_factoryMock, $menuConfig);
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
            $this->returnValue(new Iterator($this->_menuModel->getIterator()))
        );

        $this->_factoryMock->expects(
            $this->at(1)
        )->method(
            'create'
        )->with(
            $this->equalTo(['iterator' => $this->_menuSubModel->getIterator()])
        )->will(
            $this->returnValue(new Iterator($this->_menuSubModel->getIterator()))
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
