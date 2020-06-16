<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid;

use Magento\TestFramework\App\State;

/**
 * @magentoAppArea adminhtml
 * @magentoComponentsDir Magento/Backend/Block/_files/design
 * @magentoDbIsolation enabled
 */
class MassactionTest extends \PHPUnit\Framework\TestCase
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

    protected function setUp(): void
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

    protected function tearDown(): void
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
        $this->assertMatchesRegularExpression($expectedItem['url'], $actualItem->getUrl());
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
}
