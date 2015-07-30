<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order;

/**
 * Class GridTest
 */
class GridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Block\Adminhtml\Order\Grid
     */
    protected $block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->setMethods(['has'])
            ->getMockForAbstractClass();
        $this->contextMock = $this->getMockBuilder('Magento\Backend\Block\Template\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $arguments = [
            'context' => $this->contextMock
        ];
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Sales\Block\Adminhtml\Order\Grid $block */
        $this->block = $helper->getObject('Magento\Sales\Block\Adminhtml\Order\Grid', $arguments);
    }

    public function testPrepareCollection()
    {
        $collectionMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Order\Grid\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock->expects($this->any())
            ->method('has')
            ->withAnyParameters()
            ->willReturn(false);
        $layoutMock = $this->getMockBuilder('Magento\Framework\View\LayoutInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $blockMock = $this->getMockBuilder('Magento\Framework\View\Element\AbstractBlock')
            ->disableOriginalConstructor()
            ->setMethods(['getLayout'])
            ->getMockForAbstractClass();
        $blockMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $layoutMock->expects($this->any())
            ->method('getBlock')
            ->willReturn($blockMock);
        $layoutMock->expects($this->any())
            ->method('getChildName')
            ->willReturn($blockMock);
        $this->block->setData('id', 1);
        $this->block->setLayout($layoutMock);
        $this->block->setCollection($collectionMock);
        $this->assertEquals($collectionMock, $this->block->getPreparedCollection());
    }
}
