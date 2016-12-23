<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid;

use Magento\TestFramework\App\State;

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

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $mageMode;

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->mageMode = $this->objectManager->get(State::class)->getMode();

        /** @var \Magento\Theme\Model\Theme\Registration $registration */
        $registration = $this->objectManager->get(\Magento\Theme\Model\Theme\Registration::class);
        $registration->register();
        $this->objectManager->get(\Magento\Framework\View\DesignInterface::class)
            ->setDesignTheme('BackendTest/test_default');
    }

    protected function tearDown()
    {
        $this->objectManager->get(State::class)->setMode($this->mageMode);
    }

    /**
     * @param string $mageMode
     */
    private function loadLayout($mageMode = State::MODE_DEVELOPER)
    {
        $this->objectManager->get(State::class)->setMode($mageMode);
        $this->_layout = $this->objectManager->create(
            \Magento\Framework\View\LayoutInterface::class,
            ['area' => 'adminhtml']
        );
        $this->_layout->getUpdate()->load('layout_test_grid_handle');
        $this->_layout->generateXml();
        $this->_layout->generateElements();

        $this->_block = $this->_layout->getBlock('admin.test.grid.massaction');
        $this->assertNotFalse($this->_block, 'Could not load the block for testing');
    }

    public function testMassactionDefaultValues()
    {
        $this->loadLayout();

        /** @var $blockEmpty \Magento\Backend\Block\Widget\Grid\Massaction */
        $blockEmpty = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\View\LayoutInterface::class)
            ->createBlock(\Magento\Backend\Block\Widget\Grid\Massaction::class);
        $this->assertEmpty($blockEmpty->getItems());
        $this->assertEquals(0, $blockEmpty->getCount());
        $this->assertSame('[]', $blockEmpty->getItemsJson());

        $this->assertFalse($blockEmpty->isAvailable());
    }

    public function testGetJavaScript()
    {
        $this->loadLayout();

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
        $this->loadLayout();

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

    /**
     * @param string $mageMode
     * @param int $expectedCount
     * @dataProvider getCountDataProvider
     */
    public function testGetCount($mageMode, $expectedCount)
    {
        $this->loadLayout($mageMode);
        $this->assertEquals($expectedCount, $this->_block->getCount());
    }

    /**
     * @return array
     */
    public function getCountDataProvider()
    {
        return [
            [
                'mageMode' => State::MODE_DEVELOPER,
                'expectedCount' => 3,
            ],
            [
                'mageMode' => State::MODE_DEFAULT,
                'expectedCount' => 3,
            ],
            [
                'mageMode' => State::MODE_PRODUCTION,
                'expectedCount' => 2,
            ],
        ];
    }

    /**
     * @param string $itemId
     * @param array $expectedItem
     * @dataProvider getItemsDataProvider
     */
    public function testGetItems($itemId, $expectedItem)
    {
        $this->loadLayout();

        $items = $this->_block->getItems();
        $this->assertCount(3, $items);
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
            ],
            [
                'option_id3',
                [
                    'id' => 'option_id3',
                    'label' => 'Option Three',
                    'url' => '#http:\/\/localhost\/index\.php\/(?:key\/([\w\d]+)\/)?#',
                    'selected' => false,
                    'blockname' => ''
                ]
            ]
        ];
    }

    public function testGridContainsMassactionColumn()
    {
        $this->loadLayout();
        $this->_layout->getBlock('admin.test.grid')->toHtml();

        $gridMassactionColumn = $this->_layout->getBlock('admin.test.grid')
            ->getColumnSet()
            ->getChildBlock('massaction');

        $this->assertNotNull($gridMassactionColumn, 'Massaction column does not exist in the grid column set');
        $this->assertInstanceOf(
            \Magento\Backend\Block\Widget\Grid\Column::class,
            $gridMassactionColumn,
            'Massaction column is not an instance of \Magento\Backend\Block\Widget\Column'
        );
    }
}
