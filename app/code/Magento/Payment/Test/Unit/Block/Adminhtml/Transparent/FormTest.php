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
use Magento\Payment\Model\Method\TransparentInterface;

class FormTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FormTesting | \PHPUnit\Framework\MockObject\MockObject
     */
    private $form;

    /**
     * @var TransparentInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    private $methodMock;

    /**
     * @var \Magento\Checkout\Model\Session | \PHPUnit\Framework\MockObject\MockObject
     */
    private $checkoutSessionMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();

        $this->urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->setMethods(['getUrl'])
            ->getMockForAbstractClass();

        $context = $objectManagerHelper->getObject(\Magento\Framework\View\Element\Template\Context::class);

        $this->methodMock = $this->getMockBuilder(\Magento\Payment\Model\Method\TransparentInterface::class)
            ->getMock();

        $this->checkoutSessionMock = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $paymentConfigMock = $this->getMockBuilder(\Magento\Payment\Model\Config::class)
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
        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Payment::class)
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
