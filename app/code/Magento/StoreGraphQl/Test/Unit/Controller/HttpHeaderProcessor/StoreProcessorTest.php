<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Test\Unit\Controller\HttpHeaderProcessor;

use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\StoreGraphQl\Controller\HttpHeaderProcessor\StoreProcessor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class StoreProcessorTest extends TestCase
{
    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var HttpContext|MockObject
     */
    private $httpContext;

    /**
     * @var StoreCookieManagerInterface|MockObject|Stub
     */
    private $storeCookieManager;

    /**
     * @var ResolverInterface|MockObject|Stub
     */
    private $localeResolver;

    /**
     * @var LoggerInterface|MockObject|Stub
     */
    private $logger;

    /**
     * @var Http|MockObject|Stub
     */
    private $httpRequest;

    protected function setUp(): void
    {
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->httpContext = $this->createMock(HttpContext::class);
        $this->storeCookieManager = $this->createStub(StoreCookieManagerInterface::class);
        $this->localeResolver = $this->createStub(ResolverInterface::class);
        $this->logger = $this->createStub(LoggerInterface::class);
        $this->httpRequest = $this->createStub(Http::class);
    }

    public function testWithoutHeaderAndWithoutRequestedStoreUsesDefaultStoreView(): void
    {
        $this->httpContext->method('getValue')->with(StoreManagerInterface::CONTEXT_STORE)->willReturn(null);
        $this->storeManager->method('getDefaultStoreView')->willReturn($this->createStore(1, 'default'));

        $this->storeManager->expects($this->never())->method('getStore');
        $this->storeManager->expects($this->once())->method('setCurrentStore')->with('default');
        $this->httpContext->expects($this->once())
            ->method('setValue')
            ->with(StoreManagerInterface::CONTEXT_STORE, 'default', 'default');

        $this->createProcessor()->processHeaderValue('');
    }

    /**
     * @param mixed $requestedStore
     */
    #[DataProvider('requestedStoreDataProvider')]
    public function testWithoutHeaderUsesStoreResolvedFromRequestedStore($requestedStore): void
    {
        $this->httpRequest = $this->createMock(Http::class);
        $this->httpRequest->expects($this->once())
            ->method('getParam')
            ->with(StoreManagerInterface::PARAM_NAME)
            ->willReturn($requestedStore);
        $this->httpContext->method('getValue')->with(StoreManagerInterface::CONTEXT_STORE)->willReturn(null);
        $this->storeManager->method('getStore')->willReturn($this->createStore(2, 'second_store'));
        $this->storeManager->method('getDefaultStoreView')->willReturn($this->createStore(1, 'default'));

        $this->storeManager->expects($this->once())->method('setCurrentStore')->with('second_store');
        $this->httpContext->expects($this->once())
            ->method('setValue')
            ->with(StoreManagerInterface::CONTEXT_STORE, 'second_store', 'default');

        $this->createProcessor()->processHeaderValue('');
    }

    public static function requestedStoreDataProvider(): array
    {
        return [
            'code' => ['second_store'],
            'array form' => [['_data' => ['code' => 'second_store']]],
        ];
    }

    public function testWithoutHeaderReplacesResolvedAdminStoreWithDefaultStoreView(): void
    {
        $this->httpRequest->method('getParam')->willReturn('admin');
        $this->httpContext->method('getValue')->with(StoreManagerInterface::CONTEXT_STORE)->willReturn(null);
        $this->storeManager->method('getStore')->willReturn($this->createStore(0, 'admin'));
        $this->storeManager->method('getDefaultStoreView')->willReturn($this->createStore(1, 'default'));

        $this->storeManager->expects($this->once())->method('setCurrentStore')->with('default');
        $this->httpContext->expects($this->once())
            ->method('setValue')
            ->with(StoreManagerInterface::CONTEXT_STORE, 'default', 'default');

        $this->createProcessor()->processHeaderValue('');
    }

    public function testWithoutHeaderKeepsAlreadySetContext(): void
    {
        $this->httpContext->method('getValue')->with(StoreManagerInterface::CONTEXT_STORE)->willReturn('default');

        $this->storeManager->expects($this->never())->method('getStore');
        $this->storeManager->expects($this->never())->method('setCurrentStore');
        $this->httpContext->expects($this->never())->method('setValue');

        $this->createProcessor()->processHeaderValue('');
    }

    public function testHeaderSetsRequestedStore(): void
    {
        $this->localeResolver = $this->createMock(ResolverInterface::class);
        $this->storeManager->method('getStore')->willReturnMap([
            ['second_store', $this->createStore(2, 'second_store')],
        ]);
        $this->storeManager->method('getDefaultStoreView')->willReturn($this->createStore(1, 'default'));

        $this->localeResolver->expects($this->once())->method('emulate')->with(2);
        $this->storeManager->expects($this->once())->method('setCurrentStore')->with('second_store');
        $this->httpContext->expects($this->once())
            ->method('setValue')
            ->with(StoreManagerInterface::CONTEXT_STORE, 'second_store', 'default');

        $this->createProcessor()->processHeaderValue(' second_store ');
    }

    public function testHeaderWithUnknownStoreIsLoggedAndIgnored(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->storeManager->method('getStore')
            ->with('unknown_store')
            ->willThrowException(new NoSuchEntityException(__('Store not found')));

        $this->storeManager->expects($this->never())->method('setCurrentStore');
        $this->httpContext->expects($this->never())->method('setValue');
        $this->logger->expects($this->once())->method('error')->with('Store not found');

        $this->createProcessor()->processHeaderValue('unknown_store');
    }

    private function createProcessor(): StoreProcessor
    {
        return new StoreProcessor(
            $this->storeManager,
            $this->httpContext,
            $this->storeCookieManager,
            $this->localeResolver,
            $this->logger,
            $this->httpRequest
        );
    }

    private function createStore(int $id, string $code): StoreInterface
    {
        $store = $this->createStub(StoreInterface::class);
        $store->method('getId')->willReturn($id);
        $store->method('getCode')->willReturn($code);

        return $store;
    }
}
