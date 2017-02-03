<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Controller;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class PayPalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Braintree\Model\CheckoutFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutFactoryMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionFlagMock;

    /**
     * @var \Magento\Braintree\Model\Config\PayPal|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $braintreePayPalConfigMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Braintree\Controller\PayPal
     */
    protected $controller;

    /**
     * test setup
     */
    public function setUp()
    {
        $this->customerSessionMock = $this->getMockBuilder('\Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSessionMock = $this->getMockBuilder('\Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $this->braintreePayPalConfigMock = $this->getMockBuilder('\Magento\Braintree\Model\Config\PayPal')
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutFactoryMock = $this->getMockBuilder('\Magento\Braintree\Model\CheckoutFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->requestMock = $this->getMock('\Magento\Framework\App\RequestInterface');

        $this->actionFlagMock = $this->getMockBuilder('\Magento\Framework\App\ActionFlag')
            ->disableOriginalConstructor()
            ->setMethods(['set'])
            ->getMock();

        $this->resultRedirectFactoryMock = $this->getMockBuilder('\Magento\Framework\Controller\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMockBuilder('\Magento\Framework\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);
        $contextMock->expects($this->any())
            ->method('getActionFlag')
            ->willReturn($this->actionFlagMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->controller = $this->objectManagerHelper->getObject(
            'Magento\Braintree\Test\Unit\Controller\Stub\PayPalStub',
            [
                'context' => $contextMock,
                'customerSession' => $this->customerSessionMock,
                'checkoutSession' => $this->checkoutSessionMock,
                'braintreePayPalConfig' => $this->braintreePayPalConfigMock,
                'checkoutFactory' => $this->checkoutFactoryMock,
            ]
        );
    }

    public function testDispatchNotActive()
    {
        $resultRedirect = new \Magento\Framework\DataObject();
        $this->braintreePayPalConfigMock->expects($this->once())
            ->method('isActive')
            ->willReturn(false);

        $this->actionFlagMock->expects($this->once())
            ->method('set')
            ->with('', \Magento\Framework\App\ActionInterface::FLAG_NO_DISPATCH);
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirect);

        $this->assertEquals($resultRedirect, $this->controller->dispatch($this->requestMock));
        $this->assertEquals('noRoute', $resultRedirect->getPath()) ;
    }

    public function testDispatchButtonNotEnabled()
    {
        $resultRedirect = new \Magento\Framework\DataObject();
        $this->braintreePayPalConfigMock->expects($this->once())
            ->method('isActive')
            ->willReturn(true);
        $this->braintreePayPalConfigMock->expects($this->once())
            ->method('isShortcutCheckoutEnabled')
            ->willReturn(false);

        $this->actionFlagMock->expects($this->once())
            ->method('set')
            ->with('', \Magento\Framework\App\ActionInterface::FLAG_NO_DISPATCH);
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirect);

        $this->assertEquals($resultRedirect, $this->controller->dispatch($this->requestMock));
        $this->assertEquals('noRoute', $resultRedirect->getPath()) ;
    }
}
