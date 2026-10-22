<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\DirectoryGraphQl\Test\Unit\Controller\HttpRequestValidator;

use Magento\DirectoryGraphQl\Controller\HttpRequestValidator\CurrencyValidator;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreIsInactiveException;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class CurrencyValidatorTest extends TestCase
{
    /** @var StoreManagerInterface&MockObject */
    private StoreManagerInterface $storeManager;

    /** @var Http&Stub */
    private Http $request;

    /** @var CurrencyValidator */
    private CurrencyValidator $model;

    protected function setUp(): void
    {
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->request = $this->createStub(Http::class);
        $this->model = new CurrencyValidator($this->storeManager);
    }

    public function testStoreHeaderCurrencyAllowedOnRequestedStoreOnly(): void
    {
        $this->request->method('getHeader')
            ->willReturnMap([
                ['Content-Currency', 'NOK'],
                ['Store', 'website_a_store'],
            ]);

        $store = $this->createMock(Store::class);
        $store->method('getAvailableCurrencyCodes')->with(true)->willReturn(['NOK', 'EUR']);

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->with('website_a_store')
            ->willReturn($store);

        $this->model->validate($this->request);
    }

    public function testStoreHeaderCurrencyNotAllowedOnRequestedStore(): void
    {
        $this->request->method('getHeader')
            ->willReturnMap([
                ['Content-Currency', 'NOK'],
                ['Store', 'website_a_store'],
            ]);

        $store = $this->createMock(Store::class);
        $store->method('getAvailableCurrencyCodes')->with(true)->willReturn(['EUR', 'USD']);

        $this->storeManager->method('getStore')->with('website_a_store')->willReturn($store);

        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('Please correct the target currency');

        $this->model->validate($this->request);
    }

    public function testNoStoreHeaderCurrencyAllowedOnCurrentStore(): void
    {
        $this->request->method('getHeader')
            ->willReturnMap([
                ['Content-Currency', 'USD'],
                ['Store', null],
            ]);

        $store = $this->createMock(Store::class);
        $store->method('getAvailableCurrencyCodes')->with(true)->willReturn(['USD', 'EUR']);

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->with()
            ->willReturn($store);

        $this->model->validate($this->request);
    }

    public function testNoStoreHeaderCurrencyNotAllowed(): void
    {
        $this->request->method('getHeader')
            ->willReturnMap([
                ['Content-Currency', 'NOK'],
                ['Store', null],
            ]);

        $store = $this->createMock(Store::class);
        $store->method('getAvailableCurrencyCodes')->with(true)->willReturn(['USD', 'EUR']);

        $this->storeManager->method('getStore')->with()->willReturn($store);

        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('Please correct the target currency');

        $this->model->validate($this->request);
    }

    public function testStoreHeaderNamesNonExistentStore(): void
    {
        $this->request->method('getHeader')
            ->willReturnMap([
                ['Content-Currency', 'NOK'],
                ['Store', 'unknown_store'],
            ]);

        $this->storeManager->method('getStore')
            ->with('unknown_store')
            ->willThrowException(new NoSuchEntityException(__('Requested store is not found')));

        $this->storeManager->expects($this->once())->method('setCurrentStore')->with(null);

        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('Requested store is not found');

        $this->model->validate($this->request);
    }

    public function testStoreHeaderNamesInactiveStore(): void
    {
        $this->request->method('getHeader')
            ->willReturnMap([
                ['Content-Currency', 'NOK'],
                ['Store', 'inactive_store'],
            ]);

        $this->storeManager->method('getStore')
            ->with('inactive_store')
            ->willThrowException(new StoreIsInactiveException(__('Store is inactive')));

        $this->storeManager->expects($this->once())->method('setCurrentStore')->with(null);

        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('Requested store is not found');

        $this->model->validate($this->request);
    }

    public function testEmptyCurrencyHeaderSkipsStoreLookup(): void
    {
        $this->request->method('getHeader')
            ->willReturnMap([
                ['Content-Currency', ''],
                ['Store', 'website_a_store'],
            ]);

        $this->storeManager->expects($this->never())->method('getStore');

        $this->model->validate($this->request);
    }

    public function testCurrencyHeaderIsNormalizedBeforeCheck(): void
    {
        $this->request->method('getHeader')
            ->willReturnMap([
                ['Content-Currency', ' nok '],
                ['Store', 'website_a_store'],
            ]);

        $store = $this->createMock(Store::class);
        $store->method('getAvailableCurrencyCodes')->with(true)->willReturn(['NOK']);

        $this->storeManager->method('getStore')->with('website_a_store')->willReturn($store);

        $this->model->validate($this->request);
    }
}
