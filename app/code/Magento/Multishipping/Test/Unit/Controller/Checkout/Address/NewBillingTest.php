<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Controller\Checkout\Address;

use Magento\Customer\Block\Address\Edit;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use Magento\Multishipping\Controller\Checkout\Address\NewBilling;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Multishipping\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewBillingTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var NewBilling
     */
    protected $controller;

    /**
     * @var MockObject
     */
    protected $configMock;

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

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->configMock = $this->createMock(Config::class);
        $this->titleMock = $this->createMock(Title::class);
        $this->layoutMock = $this->createMock(Layout::class);
        $this->viewMock = $this->createMock(ViewInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->atLeastOnce())
            ->method('getRequest')
            ->willReturn($request);
        $contextMock->expects($this->atLeastOnce())
            ->method('getResponse')
            ->willReturn($response);
        $contextMock->expects($this->any())->method('getView')->willReturn($this->viewMock);
        $this->addressFormMock = $this->createPartialMockWithReflection(
            Edit::class,
            ['setTitle', 'setSuccessUrl', 'setErrorUrl', 'setBackUrl', 'getTitle']
        );
        $this->urlMock = $this->createMock(UrlInterface::class);
        $contextMock->expects($this->any())->method('getUrl')->willReturn($this->urlMock);
        $this->pageMock = $this->createMock(Page::class);
        $this->pageMock->expects($this->any())->method('getConfig')->willReturn($this->configMock);
        $this->configMock->expects($this->any())->method('getTitle')->willReturn($this->titleMock);
        $this->viewMock->expects($this->any())->method('getPage')->willReturn($this->pageMock);
        $this->controller = $objectManager->getObject(
            NewBilling::class,
            ['context' => $contextMock]
        );
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
        $helperMock = $this->createPartialMockWithReflection(Data::class, ['__']);
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
