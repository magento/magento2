<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Block\Adminhtml\Transparent;

use Magento\Checkout\Model\Session;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\Config;
use Magento\Payment\Model\Method\TransparentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase
{
    /**
     * @var FormTesting|MockObject
     */
    private $form;

    /**
     * @var TransparentInterface|MockObject
     */
    private $methodMock;

    /**
     * @var Session|MockObject
     */
    private $checkoutSessionMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $context = $objectManagerHelper->getObject(Context::class);

        $this->methodMock = $this->getMockBuilder(TransparentInterface::class)
            ->getMock();

        $this->checkoutSessionMock = $this->getMockBuilder(Session::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $paymentConfigMock = $this->getMockBuilder(Config::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->form = new FormTesting(
            $context,
            $paymentConfigMock,
            $this->checkoutSessionMock
        );
    }

    public function testToHtmlShouldRender()
    {
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSessionMock->expects($this->never())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $quoteMock->expects($this->never())
            ->method('getPayment')
            ->willReturn($paymentMock);
        $paymentMock->expects($this->never())
            ->method('getMethodInstance')
            ->willReturn($this->methodMock);

        $this->form->toHtml();
    }
}
