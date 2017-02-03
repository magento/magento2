<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Block\Payflow\Link;

/**
 * Test for Iframe block
 *
 */
class IframeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \Magento\Sales\Model\OrderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactoryMock;

    /**
     * @var \Magento\Paypal\Helper\Hss|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $hssHelperMock;

    /**
     * @var \Magento\Payment\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentDataMock;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \Magento\Quote\Model\Quote\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;

    /**
     * @var \Magento\Framework\Module\Dir\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reader;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readFactory;

    public function prepare()
    {
        $this->contextMock = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $this->checkoutSessionMock = $this->getMock('Magento\Checkout\Model\Session', [], [], '', false);
        $this->orderFactoryMock = $this->getMock('Magento\Sales\Model\OrderFactory', ['getQuote'], [], '', false);
        $this->hssHelperMock = $this->getMock('Magento\Paypal\Helper\Hss', [], [], '', false);
        $this->paymentDataMock = $this->getMock('Magento\Payment\Helper\Data', [], [], '', false);
        $this->quoteMock = $this->getMock('Magento\Quote\Model\Quote', ['getPayment', '__wakeup'], [], '', false);
        $this->paymentMock = $this->getMock('Magento\Quote\Model\Quote\Payment', [], [], '', false);
        $this->reader = $this->getMock('Magento\Framework\Module\Dir\Reader', [], [], '', false);
        $this->readFactory = $this->getMock('Magento\Framework\Filesystem\Directory\ReadFactory', [], [], '', false);

        $this->checkoutSessionMock->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($this->paymentMock));
        $this->hssHelperMock->expects($this->any())
            ->method('getHssMethods')
            ->will($this->returnValue([]));
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
        $methodInstance = $this->getMockBuilder('Magento\Payment\Model\MethodInterface')
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
        $methodInstance = $this->getMockBuilder('Magento\Payment\Model\MethodInterface')
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
