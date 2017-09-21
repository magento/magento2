<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\App\Action\Plugin;

use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ContextPluginTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ContextTest extends \PHPUnit\Framework\TestCase
{
    const CURRENCY_SESSION = 'CNY';
    const CURRENCY_DEFAULT = 'USD';
    const CURRENCY_CURRENT_STORE = 'UAH';

    /**
     * @var \Magento\Store\App\Action\Plugin\Context
     */
    protected $plugin;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Framework\App\Http\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpContextMock;

    /**
     * @var \Magento\Store\Model\StoreManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Store\Api\StoreCookieManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeCookieManager;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currentStoreMock;

    /**
     * @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteMock;

    /**
     * @var AbstractAction|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->sessionMock = $this->createPartialMock(\Magento\Framework\Session\Generic::class, ['getCurrencyCode']);
        $this->httpContextMock = $this->createMock(\Magento\Framework\App\Http\Context::class);
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeCookieManager = $this->createMock(\Magento\Store\Api\StoreCookieManagerInterface::class);
        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->currentStoreMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->websiteMock = $this->createPartialMock(
            \Magento\Store\Model\Website::class,
            ['getDefaultStore', '__wakeup']
        );
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)->getMockForAbstractClass();
        $this->subjectMock = $this->getMockBuilder(AbstractAction::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->plugin = (new ObjectManager($this))->getObject(
            \Magento\Store\App\Action\Plugin\Context::class,
            [
                'session' => $this->sessionMock,
                'httpContext' => $this->httpContextMock,
                'storeManager' => $this->storeManager,
                'storeCookieManager' => $this->storeCookieManager,
            ]
        );
        $this->storeManager->expects($this->once())
            ->method('getWebsite')
            ->will($this->returnValue($this->websiteMock));
        $this->storeManager->method('getDefaultStoreView')
            ->willReturn($this->storeMock);

        $this->websiteMock->expects($this->once())
            ->method('getDefaultStore')
            ->will($this->returnValue($this->storeMock));

        $this->storeCookieManager->expects($this->once())
            ->method('getStoreCodeFromCookie')
            ->will($this->returnValue('storeCookie'));
        $this->currentStoreMock->expects($this->any())
            ->method('getDefaultCurrencyCode')
            ->will($this->returnValue(self::CURRENCY_CURRENT_STORE));
    }

    public function testBeforeDispatchCurrencyFromSession()
    {
        $this->storeMock->expects($this->once())
            ->method('getDefaultCurrencyCode')
            ->will($this->returnValue(self::CURRENCY_DEFAULT));

        $this->storeMock->expects($this->once())
            ->method('getCode')
            ->willReturn('default');
        $this->currentStoreMock->expects($this->once())
            ->method('getCode')
            ->willReturn('custom_store');

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo('___store'))
            ->will($this->returnValue('default'));

        $this->storeManager->method('getStore')
            ->with('default')
            ->willReturn($this->currentStoreMock);

        $this->sessionMock->expects($this->any())
            ->method('getCurrencyCode')
            ->will($this->returnValue(self::CURRENCY_SESSION));

        $this->httpContextMock->expects($this->at(0))
            ->method('setValue')
            ->with(StoreManagerInterface::CONTEXT_STORE, 'custom_store', 'default');
        /** Make sure that current currency is taken from session if available */
        $this->httpContextMock->expects($this->at(1))
            ->method('setValue')
            ->with(Context::CONTEXT_CURRENCY, self::CURRENCY_SESSION, self::CURRENCY_DEFAULT);

        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }

    public function testDispatchCurrentStoreCurrency()
    {
        $this->storeMock->expects($this->once())
            ->method('getDefaultCurrencyCode')
            ->will($this->returnValue(self::CURRENCY_DEFAULT));

        $this->storeMock->expects($this->once())
            ->method('getCode')
            ->willReturn('default');
        $this->currentStoreMock->expects($this->once())
            ->method('getCode')
            ->willReturn('custom_store');

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo('___store'))
            ->will($this->returnValue('default'));

        $this->storeManager->method('getStore')
            ->with('default')
            ->willReturn($this->currentStoreMock);

        $this->httpContextMock->expects($this->at(0))
            ->method('setValue')
            ->with(StoreManagerInterface::CONTEXT_STORE, 'custom_store', 'default');
        /** Make sure that current currency is taken from current store if no value is provided in session */
        $this->httpContextMock->expects($this->at(1))
            ->method('setValue')
            ->with(Context::CONTEXT_CURRENCY, self::CURRENCY_CURRENT_STORE, self::CURRENCY_DEFAULT);

        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }

    public function testDispatchStoreParameterIsArray()
    {
        $this->storeMock->expects($this->once())
            ->method('getDefaultCurrencyCode')
            ->will($this->returnValue(self::CURRENCY_DEFAULT));

        $this->storeMock->expects($this->once())
            ->method('getCode')
            ->willReturn('default');
        $this->currentStoreMock->expects($this->once())
            ->method('getCode')
            ->willReturn('custom_store');

        $store = [
            '_data' => [
                'code' => 500,
            ]
        ];

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo('___store'))
            ->will($this->returnValue($store));

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->with('500')
            ->willReturn($this->currentStoreMock);

        $this->httpContextMock->expects($this->at(0))
            ->method('setValue')
            ->with(StoreManagerInterface::CONTEXT_STORE, 'custom_store', 'default');
        /** Make sure that current currency is taken from current store if no value is provided in session */
        $this->httpContextMock->expects($this->at(1))
            ->method('setValue')
            ->with(Context::CONTEXT_CURRENCY, self::CURRENCY_CURRENT_STORE, self::CURRENCY_DEFAULT);

        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid store parameter.
     */
    public function testDispatchStoreParameterIsInvalidArray()
    {
        $this->storeMock->expects($this->never())
            ->method('getDefaultCurrencyCode')
            ->will($this->returnValue(self::CURRENCY_DEFAULT));

        $this->storeMock->expects($this->never())
            ->method('getCode')
            ->willReturn('default');
        $this->currentStoreMock->expects($this->never())
            ->method('getCode')
            ->willReturn('custom_store');

        $store = [
            'some' => [
                'code' => 500,
            ]
        ];

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo('___store'))
            ->will($this->returnValue($store));
        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }
}
