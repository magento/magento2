<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Multishipping\Test\Unit\Controller\Checkout\Address;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class NewShippingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Multishipping\Controller\Checkout\Address\NewShipping
     */
    protected $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressFormMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $titleMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->configMock = $this->getMock('Magento\Framework\View\Page\Config', [], [], '', false);
        $this->checkoutMock =
            $this->getMock('Magento\Multishipping\Model\Checkout\Type\Multishipping', [], [], '', false);
        $this->titleMock = $this->getMock('Magento\Framework\View\Page\Title', [], [], '', false);
        $this->layoutMock = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $this->viewMock = $this->getMock('Magento\Framework\App\ViewInterface');
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->stateMock =
            $this->getMock('Magento\Multishipping\Model\Checkout\Type\Multishipping\State', [], [], '', false);
        $valueMap = [
            ['Magento\Multishipping\Model\Checkout\Type\Multishipping\State', $this->stateMock],
            ['Magento\Multishipping\Model\Checkout\Type\Multishipping', $this->checkoutMock]
        ];
        $this->objectManagerMock->expects($this->any())->method('get')->willReturnMap($valueMap);
        $request = $this->getMockBuilder('\Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $response = $this->getMockBuilder('\Magento\Framework\App\ResponseInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $contextMock = $this->getMock('\Magento\Framework\App\Action\Context', [], [], '', false);
        $contextMock->expects($this->atLeastOnce())
            ->method('getRequest')
            ->will($this->returnValue($request));
        $contextMock->expects($this->atLeastOnce())
            ->method('getResponse')
            ->will($this->returnValue($response));
        $contextMock->expects($this->any())->method('getView')->willReturn($this->viewMock);
        $contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $methods = ['setTitle', 'getTitle', 'setSuccessUrl', 'setBackUrl', 'setErrorUrl', '__wakeUp'];
        $this->addressFormMock =
            $this->getMock('Magento\Customer\Block\Address\Edit', $methods, [], '', false);
        $this->urlMock = $this->getMock('Magento\Framework\UrlInterface');
        $contextMock->expects($this->any())->method('getUrl')->willReturn($this->urlMock);
        $this->pageMock = $this->getMock('Magento\Framework\View\Result\Page', [], [], '', false);
        $this->pageMock->expects($this->any())->method('getConfig')->willReturn($this->configMock);
        $this->configMock->expects($this->any())->method('getTitle')->willReturn($this->titleMock);
        $this->viewMock->expects($this->any())->method('getPage')->willReturn($this->pageMock);
        $this->controller = $objectManager->getObject('Magento\Multishipping\Controller\Checkout\Address\NewShipping',
            ['context' => $contextMock]);
    }

    /**
     * @param string $backUrl
     * @param string $shippingAddress
     * @param string $url
     * @dataProvider executeDataProvider
     */
    public function testExecute($backUrl, $shippingAddress, $url)
    {
        $this->stateMock
            ->expects($this->once())
            ->method('setActiveStep')
            ->with(\Magento\Multishipping\Model\Checkout\Type\Multishipping\State::STEP_SELECT_ADDRESSES);
        $this->viewMock->expects($this->once())->method('loadLayout')->willReturnSelf();
        $this->viewMock->expects($this->any())->method('getLayout')->willReturn($this->layoutMock);
        $this->layoutMock
            ->expects($this->once())
            ->method('getBlock')
            ->with('customer_address_edit')
            ->willReturn($this->addressFormMock);
        $this->addressFormMock
            ->expects($this->once())
            ->method('setTitle')
            ->with('Create Shipping Address')
            ->willReturnSelf();
        $helperMock = $this->getMock('Magento\Multishipping\Helper\Data', [], [], '', false);
        $helperMock->expects($this->any())->method('__')->willReturn('Create Shipping Address');
        $this->addressFormMock->expects($this->once())->method('setSuccessUrl')->with('success/url')->willReturnSelf();
        $this->addressFormMock->expects($this->once())->method('setErrorUrl')->with('error/url')->willReturnSelf();
        $valueMap = [
            ['*/*/shippingSaved', null, 'success/url'],
            ['*/*/*', null, 'error/url'],
            [$backUrl, null, $url]
        ];
        $this->urlMock->expects($this->any())->method('getUrl')->willReturnMap($valueMap);
        $this->titleMock->expects($this->once())->method('getDefault')->willReturn('default_title');
        $this->addressFormMock->expects($this->once())->method('getTitle')->willReturn('Address title');
        $this->titleMock->expects($this->once())->method('set')->with('Address title - default_title');
        $this->checkoutMock
            ->expects($this->once())
            ->method('getCustomerDefaultShippingAddress')
            ->willReturn($shippingAddress);
        $this->addressFormMock->expects($this->once())->method('setBackUrl')->with($url);
        $this->viewMock->expects($this->once())->method('renderLayout');
        $this->controller->execute();
    }

    public function executeDataProvider()
    {
        return [
            'shipping_address_exists' => ['*/checkout/addresses', 'shipping_address', 'back/address'],
            'shipping_address_not_exist' => ['*/cart/', null, 'back/cart']
        ];
    }


    public function testExecuteWhenCustomerAddressBlockNotExist()
    {
        $this->stateMock
            ->expects($this->once())
            ->method('setActiveStep')
            ->with(\Magento\Multishipping\Model\Checkout\Type\Multishipping\State::STEP_SELECT_ADDRESSES);
        $this->viewMock->expects($this->once())->method('loadLayout')->willReturnSelf();
        $this->viewMock->expects($this->any())->method('getLayout')->willReturn($this->layoutMock);
        $this->layoutMock
            ->expects($this->once())
            ->method('getBlock')
            ->with('customer_address_edit');
        $this->urlMock->expects($this->never())->method('getUrl');
        $this->viewMock->expects($this->once())->method('renderLayout');
        $this->controller->execute();
    }
}
