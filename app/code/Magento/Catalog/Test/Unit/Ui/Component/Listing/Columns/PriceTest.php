<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\Component\Listing\Columns;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Ui\Component\Listing\Columns\Price;
use Magento\Directory\Model\Currency;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class PriceTest extends TestCase
{
    /**
     * @var ContextInterface|MockObject
     */
    private $context;

    /**
     * @var UiComponentFactory|MockObject
     */
    private $uiComponentFactory;

    /**
     * @var CurrencyInterface|MockObject
     */
    private $localeCurrency;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private $priceCurrency;

    /**
     * @var Price
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->context = $this->createMock(ContextInterface::class);
        $this->uiComponentFactory = $this->createMock(UiComponentFactory::class);
        $this->localeCurrency = $this->createMock(CurrencyInterface::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->priceCurrency = $this->createMock(PriceCurrencyInterface::class);
        $this->model = new Price(
            $this->context,
            $this->uiComponentFactory,
            $this->localeCurrency,
            $this->storeManager,
            [],
            ['name' => 'price'],
            $this->priceCurrency
        );
    }

    /**
     * @param array $input
     * @param array $output
     * @return void
     */
    #[DataProvider('prepareDataSourceDataProvider')]
    public function testPrepareDataSource(array $input, array $output): void
    {
        $baseCurrency = $this->createStub(Currency::class);
        $store = $this->createStub(Store::class);
        $store->method('getBaseCurrency')->willReturn($baseCurrency);
        $this->context->method('getFilterParam')
            ->with('store_id', Store::DEFAULT_STORE_ID)
            ->willReturn(2);
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->with(2)
            ->willReturn($store);
        $this->priceCurrency->expects($this->once())
            ->method('format')
            ->with('10.000000', false, PriceCurrencyInterface::DEFAULT_PRECISION, $store, $baseCurrency)
            ->willReturn('formatted');
        $this->assertEquals($output, $this->model->prepareDataSource($input));
    }

    /**
     * @return void
     */
    public function testPrepareDataSourceSkipsItemsWithoutPrice(): void
    {
        $store = $this->createStub(Store::class);
        $store->method('getBaseCurrency')->willReturn($this->createStub(Currency::class));
        $this->storeManager->method('getStore')->willReturn($store);
        $this->priceCurrency->expects($this->never())->method('format');
        $input = ['data' => ['items' => [['sku' => 'simple']]]];
        $this->assertEquals($input, $this->model->prepareDataSource($input));
    }

    /**
     * @return void
     */
    public function testPrepareDataSourceWithoutItems(): void
    {
        $this->storeManager->expects($this->never())->method('getStore');
        $this->priceCurrency->expects($this->never())->method('format');
        $input = ['data' => ['totalRecords' => 0]];
        $this->assertEquals($input, $this->model->prepareDataSource($input));
    }

    /**
     * @return array
     */
    public static function prepareDataSourceDataProvider(): array
    {
        return [
            [
                [
                    'data' => [
                        'items' => [
                            [
                                'price' => '10.00'
                            ]
                        ]
                    ]
                ],
                [
                    'data' => [
                        'items' => [
                            [
                                'price' => 'formatted'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
