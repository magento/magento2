<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Multishipping\Test\Unit\Controller\Checkout\Address;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditAddressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Multishipping\Controller\Checkout\Address\EditAddress
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

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->configMock = $this->createMock(\Magento\Framework\View\Page\Config::class);
        $this->checkoutMock =
            $this->createMock(\Magento\Multishipping\Model\Checkout\Type\Multishipping::class);
        $this->titleMock = $this->createMock(\Magento\Framework\View\Page\Title::class);
        $this->layoutMock = $this->createMock(\Magento\Framework\View\Layout::class);
        $this->viewMock = $this->createMock(\Magento\Framework\App\ViewInterface::class);
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $response = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $contextMock = $this->createMock(\Magento\Framework\App\Action\Context::class);
        $contextMock->expects($this->atLeastOnce())
            ->method('getRequest')
            ->will($this->returnValue($this->request));
        $contextMock->expects($this->atLeastOnce())
            ->method('getResponse')
            ->will($this->returnValue($response));
        $contextMock->expects($this->any())->method('getView')->willReturn($this->viewMock);
        $methods = ['setTitle', 'getTitle', 'setSuccessUrl', 'setBackUrl', 'setErrorUrl', '__wakeUp'];
        $this->addressFormMock =
            $this->createPartialMock(\Magento\Customer\Block\Address\Edit::class, $methods);
        $this->urlMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        $contextMock->expects($this->any())->method('getUrl')->willReturn($this->urlMock);
        $this->pageMock = $this->createMock(\Magento\Framework\View\Result\Page::class);
        $this->pageMock->expects($this->any())->method('getConfig')->willReturn($this->configMock);
        $this->configMock->expects($this->any())->method('getTitle')->willReturn($this->titleMock);
        $this->viewMock->expects($this->any())->method('getPage')->willReturn($this->pageMock);
        $this->controller = $objectManager->getObject(
            \Magento\Multishipping\Controller\Checkout\Address\EditAddress::class,
            ['context' => $contextMock]);
    }

    public function testExecute()
    {
        $this->viewMock->expects($this->once())->method('loadLayout')->willReturnSelf();
        $this->request->expects($this->once())->method('getParam')->with('id')->willReturn(1);
        $this->viewMock->expects($this->any())->method('getLayout')->willReturn($this->layoutMock);
        $this->layoutMock
            ->expects($this->once())
            ->method('getBlock')
            ->with('customer_address_edit')
            ->willReturn($this->addressFormMock);
        $this->addressFormMock
            ->expects($this->once())
            ->method('setTitle')
            ->with('Edit Address')
            ->willReturnSelf();
        $helperMock = $this->createPartialMock(\Magento\Multishipping\Helper\Data::class, ['__']);
        $helperMock->expects($this->any())->method('__')->willReturn('Edit Address');
        $valueMap = [
            ['*/*/selectBilling', null, 'success/url'],
            ['*/*/*', ['id' => 1], 'error/url'],
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
