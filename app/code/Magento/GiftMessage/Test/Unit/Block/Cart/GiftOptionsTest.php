<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftMessage\Test\Unit\Block\Cart;

use Magento\Backend\Block\Template\Context;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\Json\Encoder;
use Magento\GiftMessage\Block\Cart\GiftOptions;
use Magento\GiftMessage\Model\CompositeConfigProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GiftOptionsTest extends TestCase
{
    /** @var Context|MockObject */
    protected $context;

    /** @var CompositeConfigProvider|MockObject */
    protected $compositeConfigProvider;

    /** @var \Magento\Checkout\Model\CompositeConfigProvider|MockObject */
    protected $layoutProcessorMock;

    /** @var GiftOptions */
    protected $model;

    /** @var Encoder|MockObject */
    protected $jsonEncoderMock;

    /** @var array  */
    protected $jsLayout = ['root' => 'node'];

    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->jsonEncoderMock = $this->createMock(Encoder::class);
        $this->compositeConfigProvider = $this->createMock(CompositeConfigProvider::class);
        $this->layoutProcessorMock = $this->getMockForAbstractClass(
            LayoutProcessorInterface::class,
            [],
            '',
            false
        );
        $this->model = new GiftOptions(
            $this->context,
            $this->jsonEncoderMock,
            $this->compositeConfigProvider,
            [$this->layoutProcessorMock],
            ['jsLayout' => $this->jsLayout]
        );
    }

    public function testGetJsLayout()
    {
        $this->layoutProcessorMock->expects($this->once())
            ->method('process')
            ->with($this->jsLayout)
            ->willReturnArgument(0);
        $this->jsonEncoderMock->expects($this->once())
            ->method('encode')
            ->with($this->jsLayout)
            ->willReturnArgument(0);
        $this->assertEquals($this->jsLayout, $this->model->getJsLayout());
    }

    public function testGetGiftOptionsConfigJson()
    {
        $this->compositeConfigProvider->expects($this->once())
            ->method('getConfig')
            ->willReturn($this->jsLayout);
        $this->jsonEncoderMock->expects($this->once())
            ->method('encode')
            ->with($this->jsLayout)
            ->willReturnArgument(0);
        $this->assertEquals($this->jsLayout, $this->model->getGiftOptionsConfigJson());
    }
}
