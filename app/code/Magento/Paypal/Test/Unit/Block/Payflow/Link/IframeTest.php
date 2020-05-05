<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Block\Payflow\Link;

use Magento\Checkout\Model\Session;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\MethodInterface;
use Magento\Paypal\Block\Payflow\Advanced\Iframe;
use Magento\Paypal\Helper\Hss;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;
use Magento\Sales\Model\OrderFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Iframe block
 *
 */
class IframeTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Session|MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var OrderFactory|MockObject
     */
    protected $orderFactoryMock;

    /**
     * @var Hss|MockObject
     */
    protected $hssHelperMock;

    /**
     * @var Data|MockObject
     */
    protected $paymentDataMock;

    /**
     * @var Quote|MockObject
     */
    protected $quoteMock;

    /**
     * @var Payment|MockObject
     */
    protected $paymentMock;

    /**
     * @var Reader|MockObject
     */
    protected $reader;

    /**
     * @var ReadFactory|MockObject
     */
    protected $readFactory;

    public function prepare()
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->checkoutSessionMock = $this->createMock(Session::class);
        $this->orderFactoryMock = $this->getMockBuilder(OrderFactory::class)
            ->addMethods(['getQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->hssHelperMock = $this->createMock(Hss::class);
        $this->paymentDataMock = $this->createMock(Data::class);
        $this->quoteMock = $this->createPartialMock(Quote::class, ['getPayment', '__wakeup']);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->reader = $this->createMock(Reader::class);
        $this->readFactory = $this->createMock(ReadFactory::class);

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
        $block = new Iframe(
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
        $methodInstance = $this->getMockBuilder(MethodInterface::class)
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
        $methodInstance = $this->getMockBuilder(MethodInterface::class)
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
