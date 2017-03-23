<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Controller\Store;

use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreResolver;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test class for \Magento\Store\Controller\Store\SwitchAction
 */
class SwitchActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Controller\Store\SwitchAction
     */
    private $model;

    /**
     * @var StoreCookieManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeCookieManagerMock;

    /**
     * @var HttpContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpContextMock;

    /**
     * @var StoreRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeRepositoryMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectMock;

    protected function setUp()
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

        $this->model = (new ObjectManager($this))->getObject(
            \Magento\Store\Controller\Store\SwitchAction::class,
            [
                'storeCookieManager' => $this->storeCookieManagerMock,
                'httpContext' => $this->httpContextMock,
                'storeRepository' => $this->storeRepositoryMock,
                'storeManager' => $this->storeManagerMock,
                '_request' => $this->requestMock,
                '_response' => $this->responseMock,
                '_redirect' => $this->redirectMock
            ]
        );
    }

    public function testExecuteSuccessWithoutUseStoreInUrl()
    {
        $storeToSwitchToCode = 'sv2';
        $defaultStoreViewCode = 'default';
        $expectedRedirectUrl = "magento.com/{$storeToSwitchToCode}";
        $currentActiveStoreMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)->getMock();
        $defaultStoreViewMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)->getMock();
        $storeToSwitchToMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['isUseStoreInUrl'])
            ->getMockForAbstractClass();

        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($currentActiveStoreMock);
        $this->requestMock->expects($this->once())->method('getParam')->willReturn($storeToSwitchToCode);
        $this->storeRepositoryMock
            ->expects($this->once())
            ->method('getActiveStoreByCode')
            ->willReturn($storeToSwitchToMock);
        $this->storeManagerMock
            ->expects($this->once())
            ->method('getDefaultStoreView')
            ->willReturn($defaultStoreViewMock);
        $defaultStoreViewMock->expects($this->once())->method('getId')->willReturn($defaultStoreViewCode);
        $storeToSwitchToMock->expects($this->once())->method('getId')->willReturn($storeToSwitchToCode);
        $storeToSwitchToMock->expects($this->once())->method('isUseStoreInUrl')->willReturn(false);
        $this->redirectMock->expects($this->once())->method('getRedirectUrl')->willReturn($expectedRedirectUrl);
        $this->responseMock->expects($this->once())->method('setRedirect')->with($expectedRedirectUrl);

        $this->model->execute();
    }

    public function testExecuteSuccessWithUseStoreInUrl()
    {
        $currentActiveStoreCode = 'sv1';
        $storeToSwitchToCode = 'sv2';
        $defaultStoreViewCode = 'default';
        $originalRedirectUrl = "magento.com/{$currentActiveStoreCode}/test-page/test-sub-page";
        $expectedRedirectUrl = "magento.com/{$storeToSwitchToCode}/test-page/test-sub-page";
        $currentActiveStoreMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['isUseStoreInUrl', 'getBaseUrl'])
            ->getMockForAbstractClass();
        $defaultStoreViewMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)->getMock();
        $storeToSwitchToMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['isUseStoreInUrl', 'getBaseUrl'])
            ->getMockForAbstractClass();

        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($currentActiveStoreMock);
        $this->requestMock->expects($this->once())->method('getParam')->willReturn($storeToSwitchToCode);
        $this->storeRepositoryMock
            ->expects($this->once())
            ->method('getActiveStoreByCode')
            ->willReturn($storeToSwitchToMock);
        $this->storeManagerMock
            ->expects($this->once())
            ->method('getDefaultStoreView')
            ->willReturn($defaultStoreViewMock);
        $defaultStoreViewMock->expects($this->once())->method('getId')->willReturn($defaultStoreViewCode);
        $storeToSwitchToMock->expects($this->once())->method('getId')->willReturn($storeToSwitchToCode);
        $storeToSwitchToMock->expects($this->once())->method('isUseStoreInUrl')->willReturn(true);
        $this->redirectMock->expects($this->any())->method('getRedirectUrl')->willReturn($originalRedirectUrl);
        $currentActiveStoreMock
            ->expects($this->any())
            ->method('getBaseUrl')
            ->willReturn("magento.com/{$currentActiveStoreCode}");
        $storeToSwitchToMock
            ->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn("magento.com/{$storeToSwitchToCode}");
        $this->responseMock->expects($this->once())->method('setRedirect')->with($expectedRedirectUrl);

        $this->model->execute();
    }
}
