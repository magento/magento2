<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\Component\Listing\Columns;

use Magento\Catalog\Ui\Component\Listing\Columns\Price;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Locale\CurrencyInterface;
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
     * @dataProvider prepareDataSourceDataProvider
     */
    public function testPrepareDataSource(array $input, array $output): void
    {
        $this->priceCurrency->method('format')
            ->willReturn('formatted');
        $this->assertEquals($output, $this->model->prepareDataSource($input));
    }

    /**
     * @return array
     */
    public function prepareDataSourceDataProvider(): array
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
