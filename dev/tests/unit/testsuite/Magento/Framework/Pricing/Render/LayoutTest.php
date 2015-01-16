<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Pricing\Render;

/**
 * Test class for \Magento\Framework\Pricing\Render\Layout
 */
class LayoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Layout
     */
    protected $model;

    /**
     * @var  \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    /**
     * @var \Magento\Framework\View\LayoutFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $generalLayout;

    public function setUp()
    {
        $this->layout = $this->getMock('Magento\Framework\View\LayoutInterface');
        $this->generalLayout = $this->getMock('Magento\Framework\View\LayoutInterface');

        $isCacheable = false;
        $this->generalLayout->expects($this->once())
            ->method('isCacheable')
            ->will($this->returnValue(false));
        $layoutFactory = $this->getMockBuilder('Magento\Framework\View\LayoutFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $layoutFactory->expects($this->once())
            ->method('create')
            ->with($this->equalTo(['cacheable' => $isCacheable]))
            ->will($this->returnValue($this->layout));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\Framework\Pricing\Render\Layout',
            [
                'layoutFactory' => $layoutFactory,
                'generalLayout' => $this->generalLayout
            ]
        );
    }

    public function testAddHandle()
    {
        $handle = 'test_handle';

        $layoutProcessor = $this->getMock('Magento\Framework\View\Layout\ProcessorInterface');
        $layoutProcessor->expects($this->once())
            ->method('addHandle')
            ->with($handle);
        $this->layout->expects($this->once())
            ->method('getUpdate')
            ->will($this->returnValue($layoutProcessor));

        $this->model->addHandle($handle);
    }

    public function testLoadLayout()
    {
        $layoutProcessor = $this->getMock('Magento\Framework\View\Layout\ProcessorInterface');
        $layoutProcessor->expects($this->once())
            ->method('load');
        $this->layout->expects($this->once())
            ->method('getUpdate')
            ->will($this->returnValue($layoutProcessor));

        $this->layout->expects($this->once())
            ->method('generateXml');

        $this->layout->expects($this->once())
            ->method('generateElements');

        $this->model->loadLayout();
    }

    public function testGetBlock()
    {
        $blockName = 'block.name';

        $block = $this->getMock('Magento\Framework\View\Element\BlockInterface');

        $this->layout->expects($this->once())
            ->method('getBlock')
            ->with($blockName)
            ->will($this->returnValue($block));

        $this->assertEquals($block, $this->model->getBlock($blockName));
    }
}
