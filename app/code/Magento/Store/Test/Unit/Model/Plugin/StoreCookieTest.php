<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Model\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreIsInactiveException;
use \InvalidArgumentException;

/**
 * Class StoreCookieTest
 */
class StoreCookieTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\Plugin\StoreCookie
     */
    protected $plugin;

    /**
     * @var \Magento\Store\Model\StoreManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Store\Api\StoreCookieManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeCookieManagerMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Framework\App\FrontController|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeRepositoryMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->storeCookieManagerMock = $this->getMockBuilder(\Magento\Store\Api\StoreCookieManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->subjectMock = $this->getMockBuilder(\Magento\Framework\App\FrontController::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->storeRepositoryMock = $this->getMockBuilder(\Magento\Store\Api\StoreRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->plugin = (new ObjectManager($this))->getObject(
            \Magento\Store\Model\Plugin\StoreCookie::class,
            [
                'storeManager' => $this->storeManagerMock,
                'storeCookieManager' => $this->storeCookieManagerMock,
                'storeRepository' => $this->storeRepositoryMock
            ]
        );
    }

    public function testBeforeDispatchNoSuchEntity()
    {
        $storeCode = 'store';
        $this->storeManagerMock->expects($this->once())->method('getDefaultStoreView')->willReturn($this->storeMock);
        $this->storeCookieManagerMock->expects($this->once())->method('getStoreCodeFromCookie')->willReturn($storeCode);
        $this->storeRepositoryMock->expects($this->once())
            ->method('getActiveStoreByCode')
            ->willThrowException(new NoSuchEntityException);
        $this->storeCookieManagerMock->expects($this->once())->method('deleteStoreCookie')->with($this->storeMock);
        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }

    public function testBeforeDispatchStoreIsInactive()
    {
        $storeCode = 'store';
        $this->storeManagerMock->expects($this->once())->method('getDefaultStoreView')->willReturn($this->storeMock);
        $this->storeCookieManagerMock->expects($this->once())->method('getStoreCodeFromCookie')->willReturn($storeCode);
        $this->storeRepositoryMock->expects($this->once())
            ->method('getActiveStoreByCode')
            ->willThrowException(new StoreIsInactiveException);
        $this->storeCookieManagerMock->expects($this->once())->method('deleteStoreCookie')->with($this->storeMock);
        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }

    public function testBeforeDispatchInvalidArgument()
    {
        $storeCode = 'store';
        $this->storeManagerMock->expects($this->once())->method('getDefaultStoreView')->willReturn($this->storeMock);
        $this->storeCookieManagerMock->expects($this->once())->method('getStoreCodeFromCookie')->willReturn($storeCode);
        $this->storeRepositoryMock->expects($this->once())
            ->method('getActiveStoreByCode')
            ->willThrowException(new InvalidArgumentException);
        $this->storeCookieManagerMock->expects($this->once())->method('deleteStoreCookie')->with($this->storeMock);
        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }

    public function testBeforeDispatchNoStoreCookie()
    {
        $storeCode = null;
        $this->storeCookieManagerMock->expects($this->once())->method('getStoreCodeFromCookie')->willReturn($storeCode);
        $this->storeManagerMock->expects($this->never())->method('getDefaultStoreView')->willReturn($this->storeMock);
        $this->storeRepositoryMock->expects($this->never())->method('getActiveStoreByCode');
        $this->storeCookieManagerMock->expects($this->never())->method('deleteStoreCookie')->with($this->storeMock);
        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }
}
