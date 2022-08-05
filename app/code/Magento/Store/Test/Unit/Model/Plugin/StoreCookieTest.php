<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model\Plugin;

use InvalidArgumentException;
use Magento\Framework\App\FrontController;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Plugin\StoreCookie;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreIsInactiveException;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for \Magento\Store\Model\Plugin\StoreCookie class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreCookieTest extends TestCase
{
    /**
     * @var StoreCookie
     */
    protected $plugin;

    /**
     * @var StoreManager|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var StoreCookieManagerInterface|MockObject
     */
    protected $storeCookieManagerMock;

    /**
     * @var Store|MockObject
     */
    protected $storeMock;

    /**
     * @var FrontController|MockObject
     */
    protected $subjectMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var StoreRepositoryInterface|MockObject
     */
    protected $storeRepositoryMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->storeCookieManagerMock = $this->getMockBuilder(StoreCookieManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->subjectMock = $this->getMockBuilder(FrontController::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->storeRepositoryMock = $this->getMockBuilder(StoreRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->plugin = (new ObjectManager($this))->getObject(
            StoreCookie::class,
            [
                'storeManager' => $this->storeManagerMock,
                'storeCookieManager' => $this->storeCookieManagerMock,
                'storeRepository' => $this->storeRepositoryMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testBeforeDispatchNoSuchEntity()
    {
        $storeCode = 'store';
        $this->storeManagerMock->expects($this->once())
            ->method('getDefaultStoreView')
            ->willReturn($this->storeMock);
        $this->storeCookieManagerMock->expects($this->atLeastOnce())
            ->method('getStoreCodeFromCookie')
            ->willReturn($storeCode);
        $this->storeRepositoryMock->expects($this->once())
            ->method('getActiveStoreByCode')
            ->willThrowException(new NoSuchEntityException());
        $this->storeCookieManagerMock->expects($this->once())
            ->method('deleteStoreCookie')
            ->with($this->storeMock);

        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }

    /**
     * @return void
     */
    public function testBeforeDispatchStoreIsInactive()
    {
        $storeCode = 'store';
        $this->storeManagerMock->expects($this->once())
            ->method('getDefaultStoreView')
            ->willReturn($this->storeMock);
        $this->storeCookieManagerMock->expects($this->atLeastOnce())
            ->method('getStoreCodeFromCookie')
            ->willReturn($storeCode);
        $this->storeRepositoryMock->expects($this->once())
            ->method('getActiveStoreByCode')
            ->willThrowException(new StoreIsInactiveException());
        $this->storeCookieManagerMock->expects($this->once())
            ->method('deleteStoreCookie')
            ->with($this->storeMock);

        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }

    /**
     * @return void
     */
    public function testBeforeDispatchInvalidArgument()
    {
        $storeCode = 'store';
        $this->storeManagerMock->expects($this->once())
            ->method('getDefaultStoreView')
            ->willReturn($this->storeMock);
        $this->storeCookieManagerMock->expects($this->atLeastOnce())
            ->method('getStoreCodeFromCookie')
            ->willReturn($storeCode);
        $this->storeRepositoryMock->expects($this->once())
            ->method('getActiveStoreByCode')
            ->willThrowException(new InvalidArgumentException());
        $this->storeCookieManagerMock->expects($this->once())
            ->method('deleteStoreCookie')
            ->with($this->storeMock);

        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }

    /**
     * @return void
     */
    public function testBeforeDispatchNoStoreCookie()
    {
        $storeCode = null;
        $this->storeCookieManagerMock->expects($this->atLeastOnce())
            ->method('getStoreCodeFromCookie')
            ->willReturn($storeCode);
        $this->storeManagerMock->expects($this->never())
            ->method('getDefaultStoreView')
            ->willReturn($this->storeMock);
        $this->storeRepositoryMock->expects($this->never())
            ->method('getActiveStoreByCode');
        $this->storeCookieManagerMock->expects($this->never())
            ->method('deleteStoreCookie')
            ->with($this->storeMock);

        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }

    /**
     * @return void
     */
    public function testBeforeDispatchWithStoreRequestParam()
    {
        $storeCode = 'store';
        $this->storeCookieManagerMock->expects($this->atLeastOnce())
            ->method('getStoreCodeFromCookie')
            ->willReturn($storeCode);
        $this->storeRepositoryMock->expects($this->atLeastOnce())
            ->method('getActiveStoreByCode')
            ->willReturn($this->storeMock);
        $this->storeCookieManagerMock->expects($this->never())
            ->method('deleteStoreCookie')
            ->with($this->storeMock);

        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }
}
