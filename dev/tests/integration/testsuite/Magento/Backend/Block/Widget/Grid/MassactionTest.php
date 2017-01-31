<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Backend\Block\Widget\Grid;

/**
 * @magentoAppArea adminhtml
 * @magentoComponentsDir Magento/Backend/Block/_files/design
 * @magentoDbIsolation enabled
 */
class MassactionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\Widget\Grid\Massaction
     */
    protected $_block;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    protected function setUp()
    {
        $this->markTestIncomplete('MAGETWO-6406');

        parent::setUp();

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Theme\Model\Theme\Registration $registration */
        $registration = $objectManager->get('Magento\Theme\Model\Theme\Registration');
        $registration->register();
        $objectManager->get('Magento\Framework\View\DesignInterface')->setDesignTheme('BackendTest/test_default');
        $this->_layout = $objectManager->create(
            'Magento\Framework\View\LayoutInterface',
            ['area' => 'adminhtml']
        );
        $this->_layout->getUpdate()->load('layout_test_grid_handle');
        $this->_layout->generateXml();
        $this->_layout->generateElements();

        $this->_block = $this->_layout->getBlock('admin.test.grid.massaction');
        $this->assertNotFalse($this->_block, 'Could not load the block for testing');
    }

    /**
     * @covers \Magento\Backend\Block\Widget\Grid\Massaction::getItems
     * @covers \Magento\Backend\Block\Widget\Grid\Massaction::getCount
     * @covers \Magento\Backend\Block\Widget\Grid\Massaction::getItemsJson
     * @covers \Magento\Backend\Block\Widget\Grid\Massaction::isAvailable
     */
    public function testMassactionDefaultValues()
    {
        /** @var $blockEmpty \Magento\Backend\Block\Widget\Grid\Massaction */
        $blockEmpty = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Backend\Block\Widget\Grid\Massaction'
        );
        $this->assertEmpty($blockEmpty->getItems());
        $this->assertEquals(0, $blockEmpty->getCount());
        $this->assertSame('[]', $blockEmpty->getItemsJson());

        $this->assertFalse($blockEmpty->isAvailable());
    }

    public function testGetJavaScript()
    {
        $javascript = $this->_block->getJavaScript();

        $expectedItemFirst = '#"option_id1":{"label":"Option One",' .
            '"url":"http:\\\/\\\/localhost\\\/index\.php\\\/(?:key\\\/([\w\d]+)\\\/)?",' .
            '"complete":"Test","id":"option_id1"}#';
        $this->assertRegExp($expectedItemFirst, $javascript);

        $expectedItemSecond = '#"option_id2":{"label":"Option Two",' .
            '"url":"http:\\\/\\\/localhost\\\/index\.php\\\/(?:key\\\/([\w\d]+)\\\/)?",' .
            '"confirm":"Are you sure\?","id":"option_id2"}#';
        $this->assertRegExp($expectedItemSecond, $javascript);
    }

    public function testGetJavaScriptWithAddedItem()
    {
        $input = [
            'id' => 'option_id3',
            'label' => 'Option Three',
            'url' => '*/*/option3',
            'block_name' => 'admin.test.grid.massaction.option3',
        ];
        $expected = '#"option_id3":{"id":"option_id3","label":"Option Three",' .
            '"url":"http:\\\/\\\/localhost\\\/index\.php\\\/(?:key\\\/([\w\d]+)\\\/)?",' .
            '"block_name":"admin.test.grid.massaction.option3"}#';

        $this->_block->addItem($input['id'], $input);
        $this->assertRegExp($expected, $this->_block->getJavaScript());
    }

    public function testGetCount()
    {
        $this->assertEquals(2, $this->_block->getCount());
    }

    /**
     * @param $itemId
     * @param $expectedItem
     * @dataProvider getItemsDataProvider
     */
    public function testGetItems($itemId, $expectedItem)
    {
        $items = $this->_block->getItems();
        $this->assertCount(2, $items);
        $this->assertArrayHasKey($itemId, $items);

        $actualItem = $items[$itemId];
        $this->assertEquals($expectedItem['id'], $actualItem->getId());
        $this->assertEquals($expectedItem['label'], $actualItem->getLabel());
        $this->assertRegExp($expectedItem['url'], $actualItem->getUrl());
        $this->assertEquals($expectedItem['selected'], $actualItem->getSelected());
        $this->assertEquals($expectedItem['blockname'], $actualItem->getBlockName());
    }

    /**
     * @return array
     */
    public function getItemsDataProvider()
    {
        return [
            [
                'option_id1',
                [
                    'id' => 'option_id1',
                    'label' => 'Option One',
                    'url' => '#http:\/\/localhost\/index\.php\/(?:key\/([\w\d]+)\/)?#',
                    'selected' => false,
                    'blockname' => ''
                ],
            ],
            [
                'option_id2',
                [
                    'id' => 'option_id2',
                    'label' => 'Option Two',
                    'url' => '#http:\/\/localhost\/index\.php\/(?:key\/([\w\d]+)\/)?#',
                    'selected' => false,
                    'blockname' => ''
                ]
            ]
        ];
    }

    public function testGridContainsMassactionColumn()
    {
        $this->_layout->getBlock('admin.test.grid')->toHtml();

        $gridMassactionColumn = $this->_layout->getBlock(
            'admin.test.grid'
        )->getColumnSet()->getChildBlock(
            'massaction'
        );
        $this->assertNotNull($gridMassactionColumn, 'Massaction column does not exist in the grid column set');
        $this->assertInstanceOf(
            'Magento\Backend\Block\Widget\Grid\Column',
            $gridMassactionColumn,
            'Massaction column is not an instance of \Magento\Backend\Block\Widget\Column'
        );
    }
}
