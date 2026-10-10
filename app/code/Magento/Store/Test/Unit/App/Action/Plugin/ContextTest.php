<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\App\Action\Plugin;

use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Store\App\Action\Plugin\Context as ContextPlugin;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ContextTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var ContextPlugin
     */
    private $plugin;

    /**
     * @var SessionManagerInterface|MockObject
     */
    private $sessionMock;

    /**
     * @var HttpContext|MockObject
     */
    private $httpContextMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreCookieManagerInterface|MockObject
     */
    private $storeCookieManagerMock;

    /**
     * @var AbstractAction|MockObject
     */
    private $subjectMock;

    /**
     * @var HttpRequest|MockObject
     */
    private $requestMock;

    /**
     * @var Store|MockObject
     */
    private $currentStoreMock;

    /**
     * @var Store|MockObject
     */
    private $defaultStoreMock;

    /**
     * @var array
     */
    private $contextValues = [];

    protected function setUp(): void
    {
        $this->sessionMock = $this->createPartialMockWithReflection(
            SessionManagerInterface::class,
            [
                'start', 'writeClose', 'isSessionExists', 'getSessionId', 'getName', 'setName',
                'destroy', 'clearStorage', 'getCookieDomain', 'getCookiePath', 'getCookieLifetime',
                'setSessionId', 'regenerateId', 'expireSessionCookie', 'getSessionIdForHost',
                'isValidForHost', 'isValidForPath', 'getCurrencyCode'
            ]
        );
        $this->sessionMock->method('getCurrencyCode')->willReturn(null);

        $this->httpContextMock = $this->createMock(HttpContext::class);
        $this->contextValues = [];
        $this->httpContextMock->method('setValue')
            ->willReturnCallback(
                function (string $name, $value, $default) {
                    $this->contextValues[$name] = [$value, $default];
                    return $this->httpContextMock;
                }
            );

        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeCookieManagerMock = $this->createMock(StoreCookieManagerInterface::class);
        $this->storeCookieManagerMock->method('getStoreCodeFromCookie')->willReturn(null);
        $this->subjectMock = $this->createMock(AbstractAction::class);
        $this->requestMock = $this->createMock(HttpRequest::class);
        $this->requestMock->method('getParam')
            ->willReturnCallback(
                function ($name, $default = null) {
                    return $default;
                }
            );

        $this->currentStoreMock = $this->createMock(Store::class);
        $this->currentStoreMock->method('getCode')->willReturn('second_website_store');
        $this->currentStoreMock->method('getDefaultCurrencyCode')->willReturn('EUR');
        $this->currentStoreMock->method('isUseStoreInUrl')->willReturn(false);

        $this->defaultStoreMock = $this->createMock(Store::class);
        $this->defaultStoreMock->method('getCode')->willReturn('default_website_store');
        $this->defaultStoreMock->method('getDefaultCurrencyCode')->willReturn('USD');
        $this->defaultStoreMock->method('isUseStoreInUrl')->willReturn(false);

        $this->plugin = new ContextPlugin(
            $this->sessionMock,
            $this->httpContextMock,
            $this->storeManagerMock,
            $this->storeCookieManagerMock
        );
    }

    /**
     * MAGE_RUN_TYPE=website must use the default store view of the requested
     * website as context default, not the global default store view.
     *
     * Otherwise the context value always differs from the default on every
     * non-default website, the vary string is non-empty for every guest and
     * X-Magento-Vary is sent on every response, which makes all cookieless
     * requests uncacheable behind the official Varnish VCL.
     */
    public function testWebsiteRunTypeUsesWebsiteDefaultStoreAsContextDefault(): void
    {
        $this->mockRunParams(ScopeInterface::SCOPE_WEBSITE, 'second_website');
        $this->storeManagerMock->method('getStore')->with(null)->willReturn($this->currentStoreMock);

        $websiteMock = $this->createMock(Website::class);
        $websiteMock->method('getDefaultStore')->willReturn($this->currentStoreMock);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->with('second_website')
            ->willReturn($websiteMock);
        $this->storeManagerMock->expects($this->never())->method('getDefaultStoreView');

        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);

        $this->assertSame(
            ['second_website_store', 'second_website_store'],
            $this->contextValues[StoreManagerInterface::CONTEXT_STORE]
        );
        $this->assertSame(
            ['EUR', 'EUR'],
            $this->contextValues[HttpContext::CONTEXT_CURRENCY]
        );
    }

    /**
     * A store view other than the website default must still produce
     * a vary against the default store view of the same website.
     */
    public function testWebsiteRunTypeVariesForNonDefaultStoreOfWebsite(): void
    {
        $this->mockRunParams(ScopeInterface::SCOPE_WEBSITE, 'second_website');
        $this->storeManagerMock->method('getStore')->with(null)->willReturn($this->currentStoreMock);

        $websiteDefaultStoreMock = $this->createMock(Store::class);
        $websiteDefaultStoreMock->method('getCode')->willReturn('second_website_default_store');
        $websiteDefaultStoreMock->method('getDefaultCurrencyCode')->willReturn('USD');

        $websiteMock = $this->createMock(Website::class);
        $websiteMock->method('getDefaultStore')->willReturn($websiteDefaultStoreMock);
        $this->storeManagerMock->method('getWebsite')->with('second_website')->willReturn($websiteMock);

        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);

        $this->assertSame(
            ['second_website_store', 'second_website_default_store'],
            $this->contextValues[StoreManagerInterface::CONTEXT_STORE]
        );
        $this->assertSame(
            ['EUR', 'USD'],
            $this->contextValues[HttpContext::CONTEXT_CURRENCY]
        );
    }

    /**
     * MAGE_RUN_TYPE=store keeps resolving the default from MAGE_RUN_CODE.
     */
    public function testStoreRunTypeUsesRunCodeStoreAsContextDefault(): void
    {
        $this->mockRunParams(ScopeInterface::SCOPE_STORE, 'default_website_store');
        $this->storeManagerMock->method('getStore')
            ->willReturnCallback(
                function ($storeId = null) {
                    return $storeId === null ? $this->currentStoreMock : $this->defaultStoreMock;
                }
            );
        $this->storeManagerMock->expects($this->never())->method('getWebsite');

        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);

        $this->assertSame(
            ['second_website_store', 'default_website_store'],
            $this->contextValues[StoreManagerInterface::CONTEXT_STORE]
        );
        $this->assertSame(
            ['EUR', 'USD'],
            $this->contextValues[HttpContext::CONTEXT_CURRENCY]
        );
    }

    /**
     * Without run params the global default store view stays the default.
     */
    public function testNoRunTypeFallsBackToGlobalDefaultStoreView(): void
    {
        $this->mockRunParams(null, null);
        $this->storeManagerMock->method('getStore')
            ->willReturnCallback(
                function ($storeId = null) {
                    return $storeId === null ? $this->currentStoreMock : $this->defaultStoreMock;
                }
            );
        $this->storeManagerMock->expects($this->once())
            ->method('getDefaultStoreView')
            ->willReturn($this->defaultStoreMock);
        $this->storeManagerMock->expects($this->never())->method('getWebsite');

        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);

        $this->assertSame(
            ['second_website_store', 'default_website_store'],
            $this->contextValues[StoreManagerInterface::CONTEXT_STORE]
        );
    }

    /**
     * Store code in URL: the current store is its own context default.
     */
    public function testStoreCodeInUrlUsesCurrentStoreAsContextDefault(): void
    {
        $storeInUrlMock = $this->createMock(Store::class);
        $storeInUrlMock->method('getCode')->willReturn('second_website_store');
        $storeInUrlMock->method('getDefaultCurrencyCode')->willReturn('EUR');
        $storeInUrlMock->method('isUseStoreInUrl')->willReturn(true);

        $this->storeManagerMock->method('getStore')->with(null)->willReturn($storeInUrlMock);
        $this->storeManagerMock->expects($this->never())->method('getWebsite');
        $this->storeManagerMock->expects($this->never())->method('getDefaultStoreView');

        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);

        $this->assertSame(
            ['second_website_store', 'second_website_store'],
            $this->contextValues[StoreManagerInterface::CONTEXT_STORE]
        );
    }

    public function testDoesNothingWhenContextIsAlreadySet(): void
    {
        $this->httpContextMock->method('getValue')
            ->with(StoreManagerInterface::CONTEXT_STORE)
            ->willReturn('second_website_store');
        $this->httpContextMock->expects($this->never())->method('setValue');
        $this->storeManagerMock->expects($this->never())->method('getStore');

        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }

    /**
     * Mock MAGE_RUN_TYPE/MAGE_RUN_CODE server values.
     *
     * @param string|null $runType
     * @param string|null $runCode
     * @return void
     */
    private function mockRunParams(?string $runType, ?string $runCode): void
    {
        $this->requestMock->method('getServerValue')
            ->willReturnCallback(
                function ($name = null) use ($runType, $runCode) {
                    if ($name === StoreManager::PARAM_RUN_TYPE) {
                        return $runType;
                    }
                    if ($name === StoreManager::PARAM_RUN_CODE) {
                        return $runCode;
                    }
                    return null;
                }
            );
    }
}
