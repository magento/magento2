<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Pricing\Test\Unit\Render;

use \Magento\Framework\Pricing\Render\Layout;

/**
 * Test class for \Magento\Framework\Pricing\Render\Layout
 */
class LayoutTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Layout
     */
    protected $model;

    /**
     * @var  \Magento\Framework\View\LayoutInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $layout;

    /**
     * @var \Magento\Framework\View\LayoutFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $generalLayout;

    protected function setUp(): void
    {
        $this->layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $this->generalLayout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);

        $isCacheable = false;
        $this->generalLayout->expects($this->once())
            ->method('isCacheable')
            ->willReturn(false);
        $layoutFactory = $this->getMockBuilder(\Magento\Framework\View\LayoutFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $layoutFactory->expects($this->once())
            ->method('create')
            ->with($this->equalTo(['cacheable' => $isCacheable]))
            ->willReturn($this->layout);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Framework\Pricing\Render\Layout::class,
            [
                'layoutFactory' => $layoutFactory,
                'generalLayout' => $this->generalLayout
            ]
        );
    }

    public function testAddHandle()
    {
        $handle = 'test_handle';

        $layoutProcessor = $this->createMock(\Magento\Framework\View\Layout\ProcessorInterface::class);
        $layoutProcessor->expects($this->once())
            ->method('addHandle')
            ->with($handle);
        $this->layout->expects($this->once())
            ->method('getUpdate')
            ->willReturn($layoutProcessor);

        $this->model->addHandle($handle);
    }

    public function testLoadLayout()
    {
        $layoutProcessor = $this->createMock(\Magento\Framework\View\Layout\ProcessorInterface::class);
        $layoutProcessor->expects($this->once())
            ->method('load');
        $this->layout->expects($this->once())
            ->method('getUpdate')
            ->willReturn($layoutProcessor);

        $this->layout->expects($this->once())
            ->method('generateXml');

        $this->layout->expects($this->once())
            ->method('generateElements');

        $this->model->loadLayout();
    }

    public function testGetBlock()
    {
        $blockName = 'block.name';

        $block = $this->createMock(\Magento\Framework\View\Element\BlockInterface::class);

        $this->layout->expects($this->once())
            ->method('getBlock')
            ->with($blockName)
            ->willReturn($block);

        $this->assertEquals($block, $this->model->getBlock($blockName));
    }
}
