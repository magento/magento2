<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Block\Adminhtml\Transparent;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\Config;
use Magento\Payment\Model\Method\TransparentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Checkout\Model\Session;

class FormTest extends TestCase
{
    /**
     * @var FormTesting|MockObject
     */
    private $formMock;

    /**
     * @var TransparentInterface|MockObject
     */
    private $methodMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilderMock;

    /**
     * @var Session|MockObject
     */
    private $checkoutSessionMock;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();

        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->setMethods(['getUrl'])
            ->getMockForAbstractClass();

        /** @var Context $context */
        $context = $objectManagerHelper->getObject(Context::class);

        $this->methodMock = $this->getMockBuilder(TransparentInterface::class)
            ->getMock();

        $this->checkoutSessionMock = $this->getMockBuilder(Session::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Config|MockObject $paymentConfigMock */
        $paymentConfigMock = $this->getMockBuilder(Config::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->formMock = new FormTesting(
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

        $this->formMock->toHtml();
    }
}
