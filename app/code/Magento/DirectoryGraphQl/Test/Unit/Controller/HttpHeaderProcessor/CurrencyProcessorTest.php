<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\DirectoryGraphQl\Test\Unit\Controller\HttpHeaderProcessor;

use Magento\Directory\Model\Currency;
use Magento\DirectoryGraphQl\Controller\HttpHeaderProcessor\CurrencyProcessor;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CurrencyProcessorTest extends TestCase
{
    /**
     * @var StoreManagerInterface|MockObject
     */
    private StoreManagerInterface $storeManagerMock;

    /**
     * @var HttpContext|MockObject
     */
    private HttpContext $httpContextMock;

    /**
     * @var SessionManagerInterface|MockObject
     */
    private SessionManagerInterface $sessionMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private LoggerInterface $loggerMock;

    /**
     * @var Http|MockObject
     */
    private Http $requestMock;

    /**
     * @var CurrencyProcessor
     */
    private CurrencyProcessor $currencyProcessor;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->httpContextMock = $this->createMock(HttpContext::class);
        $this->sessionMock = $this->createMock(SessionManagerInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->currencyProcessor = new CurrencyProcessor(
            $this->storeManagerMock,
            $this->httpContextMock,
            $this->sessionMock,
            $this->loggerMock,
            $this->requestMock
        );
    }

    public function testProcessHeaderValueUsesDefaultCurrencyForGetRequestWithoutHeader(): void
    {
        $defaultCurrencyMock = $this->createMock(Currency::class);
        $storeMock = $this->createMock(Store::class);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getDefaultCurrency')
            ->willReturn($defaultCurrencyMock);
        $defaultCurrencyMock->expects($this->once())
            ->method('getCode')
            ->willReturn('USD');
        $storeMock->expects($this->never())
            ->method('getCurrentCurrency');
        $this->requestMock->expects($this->once())
            ->method('isGet')
            ->willReturn(true);
        $this->httpContextMock->expects($this->once())
            ->method('setValue')
            ->with(HttpContext::CONTEXT_CURRENCY, 'USD', 'USD');

        $this->currencyProcessor->processHeaderValue('');
    }

    public function testProcessHeaderValueUsesCurrentCurrencyForNonGetRequestWithoutHeader(): void
    {
        $defaultCurrencyMock = $this->createMock(Currency::class);
        $currentCurrencyMock = $this->createMock(Currency::class);
        $storeMock = $this->createMock(Store::class);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getDefaultCurrency')
            ->willReturn($defaultCurrencyMock);
        $defaultCurrencyMock->expects($this->once())
            ->method('getCode')
            ->willReturn('USD');
        $storeMock->expects($this->once())
            ->method('getCurrentCurrency')
            ->willReturn($currentCurrencyMock);
        $currentCurrencyMock->expects($this->once())
            ->method('getCode')
            ->willReturn('EUR');
        $this->requestMock->expects($this->once())
            ->method('isGet')
            ->willReturn(false);
        $this->httpContextMock->expects($this->once())
            ->method('setValue')
            ->with(HttpContext::CONTEXT_CURRENCY, 'EUR', 'USD');

        $this->currencyProcessor->processHeaderValue('');
    }
}
