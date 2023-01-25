<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Block\Widget\Grid;

use Magento\Backend\Block\Widget\Grid\Serializer;
use Magento\Catalog\Block\Adminhtml\Product\Widget\Chooser;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;

class SerializerTest extends TestCase
{
    /**
     * @var LayoutInterface
     */
    protected $_layoutMock;

    protected function setUp(): void
    {
        $this->_layoutMock = $this->getMockBuilder(
            LayoutInterface::class
        )->getMockForAbstractClass();
    }

    public function testPrepareLayout()
    {
        $objectManagerHelper = new ObjectManager($this);

        $grid = $this->createPartialMock(
            Chooser::class,
            ['getSelectedProducts']
        );
        $grid->expects($this->once())->method('getSelectedProducts')->willReturn(['product1']);
        $arguments = [
            'data' => [
                'grid_block' => $grid,
                'callback' => 'getSelectedProducts',
                'input_element_name' => 'selected_products_input',
                'reload_param_name' => 'selected_products_param',
            ],
        ];

        $block = $objectManagerHelper->getObject(Serializer::class, $arguments);
        $block->setLayout($this->_layoutMock);

        $this->assertEquals($grid, $block->getGridBlock());
        $this->assertEquals(['product1'], $block->getSerializeData());
        $this->assertEquals('selected_products_input', $block->getInputElementName());
        $this->assertEquals('selected_products_param', $block->getReloadParamName());
    }
}
