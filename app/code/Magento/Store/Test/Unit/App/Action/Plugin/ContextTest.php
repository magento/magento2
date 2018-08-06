<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\App\Action\Plugin;

use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Http\Context as HttpContext;

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
     * @var HttpContext|\PHPUnit_Framework_MockObject_MockObject
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
    protected $store;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currentStoreMock;

    /**
     * @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $website;

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
        $this->httpContextMock = $this->createMock(HttpContext::class);
        $this->httpContextMock->expects($this->once())
            ->method('getValue')
            ->with(StoreManagerInterface::CONTEXT_STORE)
            ->willReturn(null);
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeCookieManager = $this->createMock(\Magento\Store\Api\StoreCookieManagerInterface::class);
        $this->store = $this->createMock(\Magento\Store\Model\Store::class);
        $this->currentStoreMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->website = $this->createPartialMock(
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
                'storeCookieManager' => $this->storeCookieManager
            ]
        );

        $this->storeCookieManager->expects($this->once())
            ->method('getStoreCodeFromCookie')
            ->will($this->returnValue('storeCookie'));
        $this->currentStoreMock->expects($this->any())
            ->method('getDefaultCurrencyCode')
            ->will($this->returnValue(self::CURRENCY_CURRENT_STORE));
    }

    public function testBeforeDispatchCurrencyFromSession()
    {
        $this->storeManager->expects($this->once())
            ->method('getWebsite')
            ->will($this->returnValue($this->website));
        $this->website->expects($this->once())
            ->method('getDefaultStore')
            ->will($this->returnValue($this->store));

        $this->store->expects($this->once())
            ->method('getDefaultCurrencyCode')
            ->will($this->returnValue(self::CURRENCY_DEFAULT));

        $this->store->expects($this->once())
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

        $this->httpContextMock->expects($this->at(1))
            ->method('setValue')
            ->with(
                StoreManagerInterface::CONTEXT_STORE,
                'custom_store',
                'default'
            );
        // Make sure that current currency is taken from session if available.
        $this->httpContextMock->expects($this->at(2))
            ->method('setValue')
            ->with(
                Context::CONTEXT_CURRENCY,
                self::CURRENCY_SESSION,
                self::CURRENCY_DEFAULT
            );

        $this->plugin->beforeDispatch(
            $this->subjectMock,
            $this->requestMock
        );
    }

    public function testDispatchCurrentStoreAndCurrency()
    {
        $defaultStoreCode = 'default_store';
        $customStoreCode = 'custom_store';

        $this->storeManager->method('getWebsite')
            ->willReturn($this->website);
        $this->website->method('getDefaultStore')
            ->willReturn($this->store);

        $this->store->method('getDefaultCurrencyCode')
            ->willReturn(self::CURRENCY_DEFAULT);

        $this->store->method('getCode')
            ->willReturn($defaultStoreCode);
        $this->currentStoreMock->method('getCode')
            ->willReturn($customStoreCode);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo('___store'))
            ->willReturn($defaultStoreCode);

        $this->storeManager->method('getStore')
            ->with($defaultStoreCode)
            ->willReturn($this->currentStoreMock);

        $this->httpContextMock->expects($this->at(1))
            ->method('setValue')
            ->with(
                StoreManagerInterface::CONTEXT_STORE,
                $customStoreCode,
                $defaultStoreCode
            );
        // Make sure that current currency is taken from current store
        //if no value is provided in session.
        $this->httpContextMock->expects($this->at(2))
            ->method('setValue')
            ->with(
                Context::CONTEXT_CURRENCY,
                self::CURRENCY_CURRENT_STORE,
                self::CURRENCY_DEFAULT
            );

        $this->plugin->beforeDispatch(
            $this->subjectMock,
            $this->requestMock
        );
    }

    public function testDispatchStoreParameterIsArray()
    {
        $this->storeManager->expects($this->once())
            ->method('getWebsite')
            ->will($this->returnValue($this->website));
        $this->website->expects($this->once())
            ->method('getDefaultStore')
            ->will($this->returnValue($this->store));

        $this->store->expects($this->once())
            ->method('getDefaultCurrencyCode')
            ->will($this->returnValue(self::CURRENCY_DEFAULT));

        $this->store->expects($this->once())
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

        $this->httpContextMock->expects($this->at(1))
            ->method('setValue')
            ->with(
                StoreManagerInterface::CONTEXT_STORE,
                'custom_store',
                'default'
            );
        //Make sure that current currency is taken from current store
        //if no value is provided in session.
        $this->httpContextMock->expects($this->at(2))
            ->method('setValue')
            ->with(
                Context::CONTEXT_CURRENCY,
                self::CURRENCY_CURRENT_STORE,
                self::CURRENCY_DEFAULT
            );

        $this->plugin->beforeDispatch(
            $this->subjectMock,
            $this->requestMock
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\NotFoundException
     */
    public function testDispatchStoreParameterIsInvalidArray()
    {
        $this->storeManager->expects($this->once())
            ->method('getWebsite')
            ->will($this->returnValue($this->website));
        $this->website->expects($this->once())
            ->method('getDefaultStore')
            ->will($this->returnValue($this->store));
        $this->store->expects($this->exactly(2))
            ->method('getDefaultCurrencyCode')
            ->will($this->returnValue(self::CURRENCY_DEFAULT));

        $this->store->expects($this->exactly(2))
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
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->with()
            ->willReturn($this->store);
        $this->plugin->beforeDispatch(
            $this->subjectMock,
            $this->requestMock
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\NotFoundException
     */
    public function testDispatchNonExistingStore()
    {
        $storeId = 'NonExisting';
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('___store')
            ->willReturn($storeId);
        $this->storeManager->expects($this->at(0))
            ->method('getStore')
            ->with($storeId)
            ->willThrowException(new NoSuchEntityException());
        $this->storeManager->expects($this->at(1))
            ->method('getStore')
            ->with()
            ->willReturn($this->store);
        $this->storeManager->expects($this->once())
            ->method('getWebsite')
            ->will($this->returnValue($this->website));
        $this->website->expects($this->once())
            ->method('getDefaultStore')
            ->will($this->returnValue($this->store));
        $this->store->expects($this->exactly(2))
            ->method('getDefaultCurrencyCode')
            ->will($this->returnValue(self::CURRENCY_DEFAULT));

        $this->store->expects($this->exactly(2))
            ->method('getCode')
            ->willReturn('default');
        $this->currentStoreMock->expects($this->never())
            ->method('getCode')
            ->willReturn('custom_store');

        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }
}
