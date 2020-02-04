<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Block\Widget\Grid;

class SerializerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layoutMock;

    protected function setUp()
    {
        $this->_layoutMock = $this->getMockBuilder(
            \Magento\Framework\View\LayoutInterface::class
        )->getMockForAbstractClass();
    }

    public function testPrepareLayout()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $grid = $this->createPartialMock(
            \Magento\Catalog\Block\Adminhtml\Product\Widget\Chooser::class,
            ['getSelectedProducts']
        );
        $grid->expects($this->once())->method('getSelectedProducts')->will($this->returnValue(['product1']));
        $arguments = [
            'data' => [
                'grid_block' => $grid,
                'callback' => 'getSelectedProducts',
                'input_element_name' => 'selected_products_input',
                'reload_param_name' => 'selected_products_param',
            ],
        ];

        $block = $objectManagerHelper->getObject(\Magento\Backend\Block\Widget\Grid\Serializer::class, $arguments);
        $block->setLayout($this->_layoutMock);

        $this->assertEquals($grid, $block->getGridBlock());
        $this->assertEquals(['product1'], $block->getSerializeData());
        $this->assertEquals('selected_products_input', $block->getInputElementName());
        $this->assertEquals('selected_products_param', $block->getReloadParamName());
    }
}
