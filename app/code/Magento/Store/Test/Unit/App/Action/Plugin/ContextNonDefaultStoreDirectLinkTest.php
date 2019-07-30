<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Store\Test\Unit\App\Action\Plugin;

use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\Generic;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\App\Action\Plugin\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\TestCase;

/**
 * Class ContextNonDefaultStoreDirectLinkTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ContextNonDefaultStoreDirectLinkTest extends TestCase
{
    const CURRENCY_SESSION = 'CNY';
    const CURRENCY_DEFAULT = 'USD';
    const CURRENCY_CURRENT_STORE = 'UAH';

    /**
     * Test for full page cache hits from new http clients if store context was specified in the URL
     *
     * @dataProvider cacheHitOnDirectLinkToNonDefaultStoreView
     * @param string $customStore
     * @param string $defaultStore
     * @param string $expectedDefaultStore
     * @param bool $useStoreInUrl
     * @return void
     */
    public function testCacheHitOnDirectLinkToNonDefaultStoreView(
        string $customStore,
        string $defaultStore,
        string $expectedDefaultStore,
        bool $useStoreInUrl
    ) {
        $sessionMock = $this->createPartialMock(Generic::class, ['getCurrencyCode']);
        $httpContextMock = $this->createMock(HttpContext::class);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeCookieManager = $this->createMock(StoreCookieManagerInterface::class);
        $storeMock = $this->createMock(Store::class);
        $currentStoreMock = $this->createMock(Store::class);
        $requestMock = $this->getMockBuilder(RequestInterface::class)->getMockForAbstractClass();
        $subjectMock = $this->getMockBuilder(AbstractAction::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $httpContextMock->expects($this->once())
            ->method('getValue')
            ->with(StoreManagerInterface::CONTEXT_STORE)
            ->willReturn(null);

        $websiteMock = $this->createPartialMock(
            Website::class,
            ['getDefaultStore', '__wakeup']
        );

        $plugin = (new ObjectManager($this))->getObject(
            Context::class,
            [
                'session' => $sessionMock,
                'httpContext' => $httpContextMock,
                'storeManager' => $storeManager,
                'storeCookieManager' => $storeCookieManager,
            ]
        );

        $storeManager->method('getDefaultStoreView')
            ->willReturn($storeMock);

        $storeCookieManager->expects($this->once())
            ->method('getStoreCodeFromCookie')
            ->willReturn('storeCookie');

        $currentStoreMock->expects($this->any())
            ->method('getDefaultCurrencyCode')
            ->willReturn(self::CURRENCY_CURRENT_STORE);

        $currentStoreMock->expects($this->any())
            ->method('getCode')
            ->willReturn($customStore);

        $currentStoreMock->method('isUseStoreInUrl')->willReturn($useStoreInUrl);

        $storeManager->expects($this->any())
            ->method('getWebsite')
            ->willReturn($websiteMock);

        $websiteMock->expects($this->any())
            ->method('getDefaultStore')
            ->willReturn($storeMock);

        $storeMock->expects($this->any())
            ->method('getDefaultCurrencyCode')
            ->willReturn(self::CURRENCY_DEFAULT);

        $storeMock->expects($this->any())
            ->method('getCode')
            ->willReturn($defaultStore);

        $requestMock->expects($this->any())
            ->method('getParam')
            ->with($this->equalTo('___store'))
            ->willReturn($defaultStore);

        $storeManager->method('getStore')
            ->with($defaultStore)
            ->willReturn($currentStoreMock);

        $sessionMock->expects($this->any())
            ->method('getCurrencyCode')
            ->willReturn(self::CURRENCY_SESSION);

        $httpContextMock->expects($this->at(1))->method(
            'setValue'
        )->with(StoreManagerInterface::CONTEXT_STORE, $customStore, $expectedDefaultStore);

        $httpContextMock->expects($this->at(2))->method('setValue');

        $plugin->beforeDispatch(
            $subjectMock,
            $requestMock
        );
    }

    public function cacheHitOnDirectLinkToNonDefaultStoreView()
    {
        return [
            [
                'custom_store',
                'default',
                'custom_store',
                true,
            ],
            [
                'custom_store',
                'default',
                'default',
                false,
            ],
            [
                'default',
                'default',
                'default',
                true,
            ],
        ];
    }
}
