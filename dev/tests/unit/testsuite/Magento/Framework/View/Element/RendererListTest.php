<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

class RendererListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Element\RendererList
     */
    protected $renderList;

    /**
     * @var \Magento\Framework\View\Element\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Framework\View\Element\AbstractBlock|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $blockMock;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->blockMock = $this->getMockBuilder('Magento\Framework\View\Element\AbstractBlock')
            ->setMethods(['setRenderedBlock', 'getTemplate', 'setTemplate'])->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->layoutMock = $this->getMockBuilder('Magento\Framework\View\LayoutInterface')
            ->setMethods(['getBlock', 'getChildName'])->disableOriginalConstructor()->getMockForAbstractClass();

        $this->layoutMock->expects($this->any())
            ->method('getBlock')
            ->will($this->returnValue($this->blockMock));

        $this->contextMock = $this->getMockBuilder('Magento\Framework\View\Element\Context')
            ->setMethods(['getLayout'])->disableOriginalConstructor()->getMock();

        $this->contextMock->expects($this->any())
            ->method('getLayout')
            ->will($this->returnValue($this->layoutMock));

        $this->renderList = $objectManagerHelper->getObject(
            'Magento\Framework\View\Element\RendererList',
            ['context' => $this->contextMock]
        );
    }

    public function testGetRenderer()
    {
        $this->blockMock->expects($this->any())
            ->method('setRenderedBlock')
            ->will($this->returnValue($this->blockMock));

        $this->blockMock->expects($this->any())
            ->method('getTemplate')
            ->will($this->returnValue('template'));

        $this->blockMock->expects($this->any())
            ->method('setTemplate')
            ->will($this->returnValue($this->blockMock));

        $this->layoutMock->expects($this->any())
            ->method('getChildName')
            ->will($this->returnValue(true));

        /** During the first call cache will be generated */
        $this->assertInstanceOf(
            '\Magento\Framework\View\Element\BlockInterface',
            $this->renderList->getRenderer('type', null, null)
        );
        /** Cached value should be returned during second call */
        $this->assertInstanceOf(
            '\Magento\Framework\View\Element\BlockInterface',
            $this->renderList->getRenderer('type', null, 'renderer_template')
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetRendererWithException()
    {
        $this->assertInstanceOf(
            '\Magento\Framework\View\Element\BlockInterface',
            $this->renderList->getRenderer(null)
        );
    }
}
