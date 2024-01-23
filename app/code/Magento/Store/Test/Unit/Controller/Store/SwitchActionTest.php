<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Controller\Store;

use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
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
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMock();
        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['setRedirect'])
            ->getMockForAbstractClass();
        $this->redirectMock =
            $this->getMockBuilder(RedirectInterface::class)
                ->getMock();
        $this->storeSwitcher = $this->getMockBuilder(StoreSwitcher::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['switch'])
            ->getMock();

        $this->model = (new ObjectManager($this))->getObject(
            SwitchAction::class,
            [
                'storeCookieManager' => $this->storeCookieManagerMock,
                'httpContext' => $this->httpContextMock,
                'storeRepository' => $this->storeRepositoryMock,
                'storeManager' => $this->storeManagerMock,
                '_request' => $this->requestMock,
                '_response' => $this->responseMock,
                '_redirect' => $this->redirectMock,
                'storeSwitcher' => $this->storeSwitcher
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
            ->disableOriginalConstructor()
            ->addMethods(['isUseStoreInUrl'])
            ->getMockForAbstractClass();

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

        $this->model->execute();
    }
}
