<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftMessage\Test\Unit\Block\Cart\Item\Renderer\Actions;

use Magento\Backend\Block\Template\Context;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Framework\Json\Encoder;
use Magento\GiftMessage\Block\Cart\Item\Renderer\Actions\GiftOptions;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GiftOptionsTest extends TestCase
{
    /** @var GiftOptions */
    protected $model;

    /** @var Context|MockObject */
    protected $contextMock;

    /** @var LayoutProcessorInterface|MockObject */
    protected $layoutProcessorMock;

    /** @var Encoder|MockObject */
    protected $jsonEncoderMock;

    /** @var array  */
    protected $jsLayout = ['root' => 'node'];

    /**
     * @var MockObject|CompositeConfigProvider
     */
    private $compositeConfigProvider;

    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->jsonEncoderMock = $this->getMockBuilder(Encoder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->compositeConfigProvider = $this->getMockBuilder(CompositeConfigProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutProcessorMock = $this->getMockBuilder(
            LayoutProcessorInterface::class
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
         * @var Item|MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder(Item::class)
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
