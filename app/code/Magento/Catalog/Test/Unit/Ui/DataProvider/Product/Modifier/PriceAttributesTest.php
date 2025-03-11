<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Modifier\PriceAttributes;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
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
     * @dataProvider modifyDataProvider
     */
    public function testModifyData(array $input, array $output): void
    {
        $this->priceCurrency->method('format')
            ->willReturn('formatted');
        $this->assertEquals($output, $this->model->modifyData($input));
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
