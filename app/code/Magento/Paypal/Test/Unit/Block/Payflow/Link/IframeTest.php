<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Block\Payflow\Link;

/**
 * Test for Iframe block
 *
 */
class IframeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \Magento\Sales\Model\OrderFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderFactoryMock;

    /**
     * @var \Magento\Paypal\Helper\Hss|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $hssHelperMock;

    /**
     * @var \Magento\Payment\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $paymentDataMock;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteMock;

    /**
     * @var \Magento\Quote\Model\Quote\Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $paymentMock;

    /**
     * @var \Magento\Framework\Module\Dir\Reader|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $reader;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $readFactory;

    public function prepare()
    {
        $this->contextMock = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->checkoutSessionMock = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->orderFactoryMock = $this->createPartialMock(\Magento\Sales\Model\OrderFactory::class, ['getQuote']);
        $this->hssHelperMock = $this->createMock(\Magento\Paypal\Helper\Hss::class);
        $this->paymentDataMock = $this->createMock(\Magento\Payment\Helper\Data::class);
        $this->quoteMock = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['getPayment', '__wakeup']);
        $this->paymentMock = $this->createMock(\Magento\Quote\Model\Quote\Payment::class);
        $this->reader = $this->createMock(\Magento\Framework\Module\Dir\Reader::class);
        $this->readFactory = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadFactory::class);

        $this->checkoutSessionMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->any())
            ->method('getPayment')
            ->willReturn($this->paymentMock);
        $this->hssHelperMock->expects($this->any())
            ->method('getHssMethods')
            ->willReturn([]);
    }

    /**
     * Check that isScopePrivate is false
     */
    public function testCheckIsScopePrivate()
    {
        $this->prepare();
        $block = new \Magento\Paypal\Block\Payflow\Advanced\Iframe(
            $this->contextMock,
            $this->orderFactoryMock,
            $this->checkoutSessionMock,
            $this->hssHelperMock,
            $this->readFactory,
            $this->reader,
            $this->paymentDataMock
        );

        $this->assertFalse($block->isScopePrivate());
    }

    public function testGetTransactionUrlLive()
    {
        $this->prepare();

        $expected = 'https://live.url';
        $methodInstance = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->getMockForAbstractClass();
        $methodInstance->expects($this->exactly(2))
            ->method('getConfigData')
            ->willReturnMap([
                ['sandbox_flag', null, false],
                ['cgi_url', null, $expected]
            ]);
        $this->paymentDataMock->expects($this->exactly(2))
            ->method('getMethodInstance')
            ->willReturn($methodInstance);

        $block = new \Magento\Paypal\Block\Payflow\Link\Iframe(
            $this->contextMock,
            $this->orderFactoryMock,
            $this->checkoutSessionMock,
            $this->hssHelperMock,
            $this->readFactory,
            $this->reader,
            $this->paymentDataMock
        );
        $this->assertEquals($expected, $block->getTransactionUrl());
    }

    public function testGetTransactionUrlTest()
    {
        $this->prepare();

        $expected = 'https://test.url';
        $methodInstance = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->getMockForAbstractClass();
        $methodInstance->expects($this->exactly(2))
            ->method('getConfigData')
            ->willReturnMap([
                ['sandbox_flag', null, true],
                ['cgi_url_test_mode', null, $expected]
            ]);
        $this->paymentDataMock->expects($this->exactly(2))
            ->method('getMethodInstance')
            ->willReturn($methodInstance);

        $block = new \Magento\Paypal\Block\Payflow\Link\Iframe(
            $this->contextMock,
            $this->orderFactoryMock,
            $this->checkoutSessionMock,
            $this->hssHelperMock,
            $this->readFactory,
            $this->reader,
            $this->paymentDataMock
        );
        $this->assertEquals($expected, $block->getTransactionUrl());
    }
}
