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

/**
 * Test class for \Magento\Store\Controller\Store\SwitchAction
 */
class SwitchActionTest extends \PHPUnit\Framework\TestCase
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

    /** @var \Magento\Framework\Url\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    private $urlHelper;

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
        $this->urlHelper = $this->getMockBuilder(\Magento\Framework\Url\Helper\Data::class)
            ->disableOriginalConstructor()
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
                'urlHelper' => $this->urlHelper
            ]
        );
    }

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

        $this->urlHelper->expects($this->any())
            ->method('removeRequestParam')
            ->willReturn($expectedRedirectUrl);

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
        $this->redirectMock->expects($this->once())->method('getRedirectUrl')->willReturn($expectedRedirectUrl);
        $this->responseMock->expects($this->once())->method('setRedirect')->with($expectedRedirectUrl);

        $this->model->execute();
    }
}
