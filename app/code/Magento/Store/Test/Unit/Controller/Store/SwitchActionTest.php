<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Controller\Store;

use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Controller\Store\SwitchAction;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreResolver;
use Magento\Store\Model\StoreSwitcher;
use Magento\Store\Model\StoreSwitcherInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Store\Controller\Store\SwitchAction\CookieManager;
use Magento\Store\Model\StoreIsInactiveException;

/**
 * Test class for \Magento\Store\Controller\Store\SwitchAction
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SwitchActionTest extends TestCase
{
    /**
     * @var SwitchAction
     */
    private $model;

    /**
     * @var StoreCookieManagerInterface|MockObject
     */
    private $storeCookieManagerMock;

    /**
     * @var HttpContext|MockObject
     */
    private $httpContextMock;

    /**
     * @var StoreRepositoryInterface|MockObject
     */
    private $storeRepositoryMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ResponseInterface|MockObject
     */
    private $responseMock;

    /**
     * @var RedirectInterface|MockObject
     */
    private $redirectMock;

    /** @var StoreSwitcherInterface|MockObject */
    private $storeSwitcher;

    /** @var CookieManager|MockObject */
    private $cookieManagerMock;

    /** @var MessageManagerInterface|MockObject */
    private $messageManagerMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();
        $this->storeCookieManagerMock =
            $this->getMockBuilder(StoreCookieManagerInterface::class)
                ->getMock();
        $this->storeRepositoryMock =
            $this->getMockBuilder(StoreRepositoryInterface::class)
                ->getMock();
        $this->httpContextMock = $this->getMockBuilder(\Magento\Framework\App\Http\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->responseMock = $this->createMock(Http::class);
        $this->redirectMock = $this->createMock(RedirectInterface::class);
        $this->storeSwitcher = $this->getMockBuilder(StoreSwitcher::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['switch'])
            ->getMock();

        $this->cookieManagerMock = $this->createMock(CookieManager::class);
        $this->messageManagerMock = $this->createMock(MessageManagerInterface::class);

        $contextMock = $this->createMock(\Magento\Framework\App\Action\Context::class);
        $contextMock->method('getMessageManager')->willReturn($this->messageManagerMock);
        $contextMock->method('getRequest')->willReturn($this->requestMock);
        $contextMock->method('getResponse')->willReturn($this->responseMock);
        $contextMock->method('getRedirect')->willReturn($this->redirectMock);
        $contextMock->method('getActionFlag')->willReturn(
            $this->createMock(\Magento\Framework\App\ActionFlag::class)
        );
        $contextMock->method('getUrl')->willReturn(
            $this->createMock(\Magento\Framework\UrlInterface::class)
        );
        $contextMock->method('getObjectManager')->willReturn(
            $this->createMock(\Magento\Framework\ObjectManagerInterface::class)
        );
        $contextMock->method('getEventManager')->willReturn(
            $this->createMock(\Magento\Framework\Event\ManagerInterface::class)
        );
        $contextMock->method('getView')->willReturn(
            $this->createMock(\Magento\Framework\App\ViewInterface::class)
        );

        $this->model = (new ObjectManager($this))->getObject(
            SwitchAction::class,
            [
                'context' => $contextMock,
                'storeCookieManager' => $this->storeCookieManagerMock,
                'httpContext' => $this->httpContextMock,
                'storeRepository' => $this->storeRepositoryMock,
                'storeManager' => $this->storeManagerMock,
                '_request' => $this->requestMock,
                '_response' => $this->responseMock,
                '_redirect' => $this->redirectMock,
                'storeSwitcher' => $this->storeSwitcher,
                'cookieManager' => $this->cookieManagerMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $storeToSwitchToCode = 'sv2';
        $defaultStoreViewCode = 'default';
        $expectedRedirectUrl = "magento.com/{$storeToSwitchToCode}";
        $defaultStoreViewMock = $this->getMockBuilder(StoreInterface::class)
            ->getMock();
        $storeToSwitchToMock = $this->getMockBuilder(StoreInterface::class)
            ->getMock();

        $this->requestMock->expects($this->any())->method('getParam')->willReturnMap(
            [
                [StoreResolver::PARAM_NAME, null, $storeToSwitchToCode],
                ['___from_store', null, $defaultStoreViewCode]
            ]
        );
        $this->storeRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($defaultStoreViewCode)
            ->willReturn($defaultStoreViewMock);
        $this->storeRepositoryMock
            ->expects($this->once())
            ->method('getActiveStoreByCode')
            ->with($storeToSwitchToCode)
            ->willReturn($storeToSwitchToMock);
        $this->storeSwitcher->expects($this->once())
            ->method('switch')
            ->with($defaultStoreViewMock, $storeToSwitchToMock, $expectedRedirectUrl)
            ->willReturn($expectedRedirectUrl);

        $this->redirectMock->expects($this->once())->method('getRedirectUrl')->willReturn($expectedRedirectUrl);
        $this->responseMock->expects($this->once())->method('setRedirect')->with($expectedRedirectUrl);

        $this->storeCookieManagerMock->method('getStoreCodeFromCookie')->willReturn(null);

        $this->model->execute();
    }

    public function testExecuteAddsErrorAndSetsCookieForCurrentStoreWhenTargetStoreNotFound(): void
    {
        $storeToSwitchToCode = 'nonexistent';
        $fromStoreCode = 'default';
        $requestedUrl = 'https://example.test/';

        // getParam('___from_store', getStoreCodeFromCookie()) — second arg must match mocked cookie value
        $this->storeCookieManagerMock->method('getStoreCodeFromCookie')->willReturn($fromStoreCode);
        $this->requestMock->method('getParam')->willReturnMap([
            [StoreManagerInterface::PARAM_NAME, null, $storeToSwitchToCode],
            ['___from_store', $fromStoreCode, $fromStoreCode],
        ]);
        $this->redirectMock->method('getRedirectUrl')->willReturn($requestedUrl);

        $fromStoreMock = $this->createMock(StoreInterface::class);
        $this->storeRepositoryMock->method('get')->with($fromStoreCode)->willReturn($fromStoreMock);
        $this->storeRepositoryMock->method('getActiveStoreByCode')->with($storeToSwitchToCode)
            ->willThrowException(new NoSuchEntityException(__('not found')));

        $currentStoreMock = $this->createMock(StoreInterface::class);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($currentStoreMock);

        $this->messageManagerMock->expects($this->once())->method('addErrorMessage');
        $this->cookieManagerMock->expects($this->once())->method('setCookieForStore')->with($currentStoreMock);

        $this->storeSwitcher->expects($this->never())->method('switch');
        $this->responseMock->expects($this->once())->method('setRedirect')->with($requestedUrl);

        $this->model->execute();
    }
}
