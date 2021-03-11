<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Controller\Store;

use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreResolver;
use Magento\Store\Model\StoreSwitcher;
use Magento\Store\Model\StoreSwitcherInterface;

/**
 * Test class for \Magento\Store\Controller\Store\SwitchAction
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SwitchActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Controller\Store\SwitchAction
     */
    private $model;

    /**
     * @var StoreCookieManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeCookieManagerMock;

    /**
     * @var HttpContext|\PHPUnit\Framework\MockObject\MockObject
     */
    private $httpContextMock;

    /**
     * @var StoreRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeRepositoryMock;

    /**
     * @var StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $responseMock;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $redirectMock;

    /** @var StoreSwitcherInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $storeSwitcher;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)->getMock();
        $this->storeCookieManagerMock =
            $this->getMockBuilder(\Magento\Store\Api\StoreCookieManagerInterface::class)->getMock();
        $this->storeRepositoryMock =
            $this->getMockBuilder(\Magento\Store\Api\StoreRepositoryInterface::class)->getMock();
        $this->httpContextMock = $this->getMockBuilder(\Magento\Framework\App\Http\Context::class)->getMock();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRedirect'])
            ->getMockForAbstractClass();
        $this->redirectMock =
            $this->getMockBuilder(\Magento\Framework\App\Response\RedirectInterface::class)->getMock();
        $this->storeSwitcher = $this->getMockBuilder(StoreSwitcher::class)
            ->disableOriginalConstructor()
            ->setMethods(['switch'])
            ->getMock();

        $this->model = (new ObjectManager($this))->getObject(
            \Magento\Store\Controller\Store\SwitchAction::class,
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
        $defaultStoreViewMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)->getMock();
        $storeToSwitchToMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['isUseStoreInUrl'])
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
