<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->_menuModel = new Menu($logger);
        $this->_menuSubModel = new Menu($logger);

        $this->_factoryMock = $this->createPartialMock(
            IteratorFactory::class,
            ['create']
        );

        $itemOne = $this->createMock(Item::class);
        $itemOne->expects($this->any())->method('getId')->willReturn('item1');
        $itemOne->expects($this->any())->method('getTitle')->willReturn('Item 1');
        $itemOne->expects($this->any())->method('isAllowed')->willReturn(true);
        $itemOne->expects($this->any())->method('isDisabled')->willReturn(false);
        $itemOne->expects($this->any())->method('getAction')->willReturn('adminhtml/item1');
        $itemOne->expects($this->any())->method('getChildren')->willReturn($this->_menuSubModel);
        $itemOne->expects($this->any())->method('hasChildren')->willReturn(true);
        $this->_menuModel->add($itemOne);

        $itemTwo = $this->createMock(Item::class);
        $itemTwo->expects($this->any())->method('getId')->willReturn('item2');
        $itemTwo->expects($this->any())->method('getTitle')->willReturn('Item 2');
        $itemTwo->expects($this->any())->method('isAllowed')->willReturn(true);
        $itemTwo->expects($this->any())->method('isDisabled')->willReturn(false);
        $itemTwo->expects($this->any())->method('getAction')->willReturn('adminhtml/item2');
        $itemTwo->expects($this->any())->method('hasChildren')->willReturn(false);
        $this->_menuSubModel->add($itemTwo);

        $menuConfig = $this->createMock(Config::class);
        $menuConfig->expects($this->once())->method('getMenu')->willReturn($this->_menuModel);

        $this->_model = new Page($this->_factoryMock, $menuConfig);
    }

    /**
     * @return void
     */
    public function testToOptionArray(): void
    {
        $this->_factoryMock
            ->method('create')
            ->willReturnCallback(function ($arg1) {
                if ($arg1['iterator'] == $this->_menuModel->getIterator()) {
                    return new Iterator($this->_menuModel->getIterator());
                } elseif ($arg1['iterator'] == $this->_menuSubModel->getIterator()) {
                    return new Iterator($this->_menuSubModel->getIterator());
                }
            });

        $nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
        $paddingString = str_repeat($nonEscapableNbspChar, 4);

        $expected = [
            ['label' => 'Item 1', 'value' => 'item1'],
            ['label' => $paddingString . 'Item 2', 'value' => 'item2'],
        ];
        $this->assertEquals($expected, $this->_model->toOptionArray());
    }
}
