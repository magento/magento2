<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Block\Adminhtml\Transparent;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Payment\Model\Method\TransparentInterface;

class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FormTesting | \PHPUnit_Framework_MockObject_MockObject
     */
    private $form;

    /**
     * @var TransparentInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $methodMock;

    /**
     * @var \Magento\Checkout\Model\Session | \PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutSessionMock;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder('\Magento\Framework\App\RequestInterface')
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();

        $this->urlBuilderMock = $this->getMockBuilder('\Magento\Framework\UrlInterface')
            ->setMethods(['getUrl'])
            ->getMockForAbstractClass();

        $context = $objectManagerHelper->getObject('Magento\Framework\View\Element\Template\Context');

        $this->methodMock = $this->getMockBuilder('Magento\Payment\Model\Method\TransparentInterface')
            ->getMock();

        $this->checkoutSessionMock = $this->getMockBuilder('Magento\Checkout\Model\Session')
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $paymentConfigMock = $this->getMockBuilder('Magento\Payment\Model\Config')
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
        $quoteMock = $this->getMockBuilder('Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Payment')
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
