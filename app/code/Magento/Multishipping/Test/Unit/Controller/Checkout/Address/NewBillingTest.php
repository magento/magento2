<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Multishipping\Test\Unit\Controller\Checkout\Address;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class NewBillingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Multishipping\Controller\Checkout\Address\NewBilling
     */
    protected $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

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


    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->configMock = $this->getMock('Magento\Framework\View\Page\Config', [], [], '', false);
        $this->titleMock = $this->getMock('Magento\Framework\View\Page\Title', [], [], '', false);
        $this->layoutMock = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $this->viewMock = $this->getMock('Magento\Framework\App\ViewInterface');
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
        $methods = ['setTitle', 'getTitle', 'setSuccessUrl', 'setErrorUrl', 'setBackUrl', '__wakeUp'];
        $this->addressFormMock =
            $this->getMock('Magento\Customer\Block\Address\Edit', $methods, [], '', false);
        $this->urlMock = $this->getMock('Magento\Framework\UrlInterface');
        $contextMock->expects($this->any())->method('getUrl')->willReturn($this->urlMock);
        $this->pageMock = $this->getMock('Magento\Framework\View\Result\Page', [], [], '', false);
        $this->pageMock->expects($this->any())->method('getConfig')->willReturn($this->configMock);
        $this->configMock->expects($this->any())->method('getTitle')->willReturn($this->titleMock);
        $this->viewMock->expects($this->any())->method('getPage')->willReturn($this->pageMock);
        $this->controller = $objectManager->getObject('Magento\Multishipping\Controller\Checkout\Address\NewBilling',
            ['context' => $contextMock]);
    }

    public function testExecute()
    {
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
            ->with('Create Billing Address')
            ->willReturnSelf();
        $helperMock = $this->getMock('Magento\Multishipping\Helper\Data', [], [], '', false);
        $helperMock->expects($this->any())->method('__')->willReturn('Create Billing Address');
        $valueMap = [
            ['*/*/selectBilling', null, 'success/url'],
            ['*/*/*', null, 'error/url'],
        ];
        $this->urlMock->expects($this->any())->method('getUrl')->willReturnMap($valueMap);
        $this->addressFormMock->expects($this->once())->method('setSuccessUrl')->with('success/url')->willReturnSelf();
        $this->addressFormMock->expects($this->once())->method('setErrorUrl')->with('error/url')->willReturnSelf();

        $this->titleMock->expects($this->once())->method('getDefault')->willReturn('default_title');
        $this->addressFormMock->expects($this->once())->method('getTitle')->willReturn('Address title');
        $this->titleMock->expects($this->once())->method('set')->with('Address title - default_title');
        $this->addressFormMock->expects($this->once())->method('setBackUrl')->with('success/url');
        $this->viewMock->expects($this->once())->method('renderLayout');
        $this->controller->execute();
    }


    public function testExecuteWhenCustomerAddressBlockNotExist()
    {
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
