<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Test\Unit\Block\Cart\Item\Renderer\Actions;

use Magento\Backend\Block\Template\Context;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\Json\Encoder;
use Magento\GiftMessage\Block\Cart\Item\Renderer\Actions\GiftOptions;
use Magento\Quote\Model\Quote\Item;

class GiftOptionsTest extends \PHPUnit_Framework_TestCase
{
    /** @var GiftOptions */
    protected $model;

    /** @var Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var LayoutProcessorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $layoutProcessorMock;

    /** @var Encoder|\PHPUnit_Framework_MockObject_MockObject */
    protected $jsonEncoderMock;

    /** @var array  */
    protected $jsLayout = ['root' => 'node'];

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Backend\Block\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->jsonEncoderMock = $this->getMockBuilder(\Magento\Framework\Json\Encoder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->compositeConfigProvider = $this->getMockBuilder(\Magento\Checkout\Model\CompositeConfigProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutProcessorMock = $this->getMockBuilder(
            \Magento\Checkout\Block\Checkout\LayoutProcessorInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->model = new GiftOptions(
            $this->contextMock,
            $this->jsonEncoderMock,
            [$this->layoutProcessorMock],
            ['jsLayout' => $this->jsLayout]
        );
    }

    public function testGetJsLayout()
    {
        /**
         * @var Item|\PHPUnit_Framework_MockObject_MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutProcessorMock->expects($this->once())
            ->method('process')
            ->with($this->jsLayout, $itemMock)
            ->willReturnArgument(0);

        $this->jsonEncoderMock->expects($this->once())
            ->method('encode')
            ->with($this->jsLayout)
            ->willReturnArgument(0);

        $this->model->setItem($itemMock);
        $this->assertEquals($this->jsLayout, $this->model->getJsLayout());
    }
}
