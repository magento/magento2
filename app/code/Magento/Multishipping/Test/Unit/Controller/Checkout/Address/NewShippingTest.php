<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Controller\Checkout\Address;

use Magento\Customer\Block\Address\Edit;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use Magento\Multishipping\Controller\Checkout\Address\NewShipping;
use Magento\Multishipping\Helper\Data;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewShippingTest extends TestCase
{
    /**
     * @var NewShipping
     */
    protected $controller;

    /**
     * @var MockObject
     */
    protected $configMock;

    /**
     * @var MockObject
     */
    protected $objectManagerMock;

    /**
     * @var MockObject
     */
    protected $stateMock;

    /**
     * @var MockObject
     */
    protected $viewMock;

    /**
     * @var MockObject
     */
    protected $layoutMock;

    /**
     * @var MockObject
     */
    protected $addressFormMock;

    /**
     * @var MockObject
     */
    protected $urlMock;

    /**
     * @var MockObject
     */
    protected $pageMock;

    /**
     * @var MockObject
     */
    protected $titleMock;

    /**
     * @var MockObject
     */
    protected $checkoutMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->configMock = $this->createMock(Config::class);
        $this->checkoutMock =
            $this->createMock(Multishipping::class);
        $this->titleMock = $this->createMock(Title::class);
        $this->layoutMock = $this->createMock(Layout::class);
        $this->viewMock = $this->getMockForAbstractClass(ViewInterface::class);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->stateMock =
            $this->createMock(State::class);
        $valueMap = [
            [State::class, $this->stateMock],
            [Multishipping::class, $this->checkoutMock]
        ];
        $this->objectManagerMock->expects($this->any())->method('get')->willReturnMap($valueMap);
        $request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->atLeastOnce())
            ->method('getRequest')
            ->willReturn($request);
        $contextMock->expects($this->atLeastOnce())
            ->method('getResponse')
            ->willReturn($response);
        $contextMock->expects($this->any())->method('getView')->willReturn($this->viewMock);
        $contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->addressFormMock =
            $this->getMockBuilder(Edit::class)
                ->addMethods(['setTitle', 'setSuccessUrl', 'setBackUrl', 'setErrorUrl'])
                ->onlyMethods(['getTitle'])
                ->disableOriginalConstructor()
                ->getMock();
        $this->urlMock = $this->getMockForAbstractClass(UrlInterface::class);
        $contextMock->expects($this->any())->method('getUrl')->willReturn($this->urlMock);
        $this->pageMock = $this->createMock(Page::class);
        $this->pageMock->expects($this->any())->method('getConfig')->willReturn($this->configMock);
        $this->configMock->expects($this->any())->method('getTitle')->willReturn($this->titleMock);
        $this->viewMock->expects($this->any())->method('getPage')->willReturn($this->pageMock);
        $this->controller = $objectManager->getObject(
            NewShipping::class,
            ['context' => $contextMock]
        );
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
            ->with(State::STEP_SELECT_ADDRESSES);
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
        $helperMock = $this->getMockBuilder(Data::class)
            ->addMethods(['__'])
            ->disableOriginalConstructor()
            ->getMock();
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

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            'shipping_address_exists' => ['*/checkout/addresses', 'shipping_address', 'back/address'],
            'shipping_address_not_exist' => ['checkout/cart/', null, 'back/cart']
        ];
    }

    public function testExecuteWhenCustomerAddressBlockNotExist()
    {
        $this->stateMock
            ->expects($this->once())
            ->method('setActiveStep')
            ->with(State::STEP_SELECT_ADDRESSES);
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
