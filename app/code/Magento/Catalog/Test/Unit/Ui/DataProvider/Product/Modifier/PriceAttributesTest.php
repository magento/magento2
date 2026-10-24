<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Modifier;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Ui\DataProvider\Product\Modifier\PriceAttributes;
use Magento\Directory\Model\Currency;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Monolog\Test\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class PriceAttributesTest extends TestCase
{
    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var CurrencyInterface|MockObject
     */
    private $localeCurrency;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private $priceCurrency;

    /**
     * @var PriceAttributes
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->localeCurrency = $this->createMock(CurrencyInterface::class);
        $this->priceCurrency = $this->createMock(PriceCurrencyInterface::class);
        $this->model = new PriceAttributes(
            $this->storeManager,
            $this->localeCurrency,
            ['attr1', 'attr3'],
            ['type2'],
            $this->priceCurrency
        );
    }

    /**
     * @param array $input
     * @param array $output
     * @return void
     */
    #[DataProvider('modifyDataProvider')]
    public function testModifyData(array $input, array $output): void
    {
        $store = $this->createStub(Store::class);
        $store->method('getBaseCurrency')->willReturn($this->createStub(Currency::class));
        $this->storeManager->method('getStore')->willReturn($store);
        $this->priceCurrency->method('format')
            ->willReturn('formatted');
        $this->assertEquals($output, $this->model->modifyData($input));
    }

    /**
     * @return void
     */
    public function testModifyDataFormatsInBaseCurrencyOfItemStore(): void
    {
        $baseCurrency = $this->createStub(Currency::class);
        $store = $this->createStub(Store::class);
        $store->method('getBaseCurrency')->willReturn($baseCurrency);
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->with(2)
            ->willReturn($store);
        $this->priceCurrency->expects($this->once())
            ->method('format')
            ->with('11.000000', false, PriceCurrencyInterface::DEFAULT_PRECISION, $store, $baseCurrency)
            ->willReturn('formatted');
        $this->assertEquals(
            ['items' => [['type_id' => 'type1', 'store_id' => 2, 'attr1' => 'formatted']]],
            $this->model->modifyData(['items' => [['type_id' => 'type1', 'store_id' => 2, 'attr1' => '11']]])
        );
    }

    /**
     * @return array
     */
    public static function modifyDataProvider(): array
    {
        return [
            [
                [
                    'items' => [
                        [
                            'type_id' => 'type1',
                            'attr1' => '11',
                            'attr2' => '111',
                            'attr3' => '1111',
                        ],
                        [
                            'type_id' => 'type2',
                            'attr1' => '22',
                            'attr2' => '222',
                            'attr3' => '2222',
                        ],
                        [
                            'type_id' => 'type3',
                            'attr1' => '33',
                            'attr2' => '333',
                            'attr3' => '3333',
                        ]
                    ]
                ],
                [
                    'items' => [
                        [
                            'type_id' => 'type1',
                            'attr1' => 'formatted',
                            'attr2' => '111',
                            'attr3' => 'formatted',
                        ],
                        [
                            'type_id' => 'type2',
                            'attr1' => '22',
                            'attr2' => '222',
                            'attr3' => '2222',
                        ],
                        [
                            'type_id' => 'type3',
                            'attr1' => 'formatted',
                            'attr2' => '333',
                            'attr3' => 'formatted',
                        ]
                    ]
                ]
            ]
        ];
    }
}
