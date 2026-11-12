<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Webapi;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Webapi\ProductOutputProcessor;
use Magento\Framework\Webapi\Request;
use Magento\Framework\Webapi\Rest\Request\DeserializerInterface;
use PHPUnit\Framework\TestCase;

class ProductOutputProcessorTest extends TestCase
{
    /**
     * @var Request
     */
    private $requestMock;

    /**
     * @var DeserializerInterface
     */
    private $deserializerMock;

    /**
     * @var ProductOutputProcessor
     */
    private $productOutputProcessor;

    protected function setUp(): void
    {
        $this->requestMock = $this->createStub(Request::class);
        $this->deserializerMock = $this->createMock(DeserializerInterface::class);
        $this->productOutputProcessor = new ProductOutputProcessor($this->requestMock, $this->deserializerMock);
    }

    /**
     * @param $request
     * @param $product
     * @param $result
     * @param $expectedResult
     */
    #[DataProvider('getProductProcessorDataProvider')]
    public function testGetByProductType(
        array $request,
        $product,
        array $result,
        array $expectedResult
    ) {
        if (is_callable($product)) {
            $product = $product($this);
        }
        $this->requestMock
            ->method('getContent')
            ->willReturn(json_encode($request));
        $this->deserializerMock
            ->expects($this->once())
            ->method('deserialize')
            ->willReturn($request);
        $this->assertEquals($expectedResult, $this->productOutputProcessor->execute($product, $result));
    }

    public function testProcessesEachProductWithOneDeserialization(): void
    {
        $request = [
            ['product' => ['sku' => 'omitted']],
            ['product' => ['sku' => 'explicit', 'tier_prices' => [], 'product_links' => []]],
        ];
        $this->requestMock->method('getContent')->willReturn(json_encode($request));
        $this->deserializerMock->expects($this->once())->method('deserialize')->willReturn($request);
        $result = ['tier_prices' => [], 'product_links' => []];
        foreach (['omitted' => [], 'explicit' => $result, 'missing' => $result] as $sku => $expected) {
            $product = $this->createStub(ProductInterface::class);
            $product->method('getSku')->willReturn($sku);
            $product->method('getTierPrices')->willReturn([]);
            $product->method('getProductLinks')->willReturn([]);
            $this->assertSame($expected, $this->productOutputProcessor->execute($product, $result));
        }
    }

    #[DataProvider('skuMatchingDataProvider')]
    public function testPreservesSkuMatching(array $request, $sku, array $expected): void
    {
        $this->requestMock->method('getContent')->willReturn(json_encode($request));
        $this->deserializerMock->expects($this->once())->method('deserialize')->willReturn($request);
        $product = $this->createStub(ProductInterface::class);
        $product->method('getSku')->willReturn($sku);
        $product->method('getTierPrices')->willReturn([]);
        $product->method('getProductLinks')->willReturn([]);
        $this->assertSame(
            $expected,
            $this->productOutputProcessor->execute($product, ['tier_prices' => [], 'product_links' => []])
        );
    }

    public static function skuMatchingDataProvider(): array
    {
        $fields = ['tier_prices' => [], 'product_links' => []];
        return [
            'first duplicate omits fields' => [
                [['product' => ['sku' => 'same']], ['product' => ['sku' => 'same'] + $fields]],
                'same',
                [],
            ],
            'first duplicate includes fields' => [
                [['product' => ['sku' => 'same'] + $fields], ['product' => ['sku' => 'same']]],
                'same',
                $fields,
            ],
            'numeric and string SKU are distinct' => [
                [['product' => ['sku' => 123]], ['product' => ['sku' => '123'] + $fields]],
                '123',
                $fields,
            ],
        ];
    }

    public function testResetsRequestProductsBetweenRequests(): void
    {
        $first = ['product' => ['sku' => 'same']];
        $second = ['product' => ['sku' => 'same', 'tier_prices' => [], 'product_links' => []]];
        $this->requestMock->method('getContent')->willReturnOnConsecutiveCalls(
            json_encode($first),
            json_encode($second)
        );
        $this->deserializerMock->expects($this->exactly(2))->method('deserialize')
            ->willReturnOnConsecutiveCalls($first, $second);
        $product = $this->createStub(ProductInterface::class);
        $product->method('getSku')->willReturn('same');
        $product->method('getTierPrices')->willReturn([]);
        $product->method('getProductLinks')->willReturn([]);
        $result = ['tier_prices' => [], 'product_links' => []];

        $this->assertSame([], $this->productOutputProcessor->execute($product, $result));
        $this->productOutputProcessor->_resetState();
        $this->assertSame($result, $this->productOutputProcessor->execute($product, $result));
    }

    /**
     * Product data provider
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function getProductProcessorDataProvider()
    {
        return [
            'request object contains `product_links` and `tier_prices`' => [
                'request' => [
                    [
                        'product' => [
                            'sku' => 'MH01',
                            'status' => 1,
                            'product_links' => [],
                            'tier_prices' => []
                        ]
                    ]
                ],
                'product' => static fn (self $testCase) => $testCase->setProductInformation(
                    [
                        'sku' => 'MH01',
                        'status' => 1,
                        'product_links' => [],
                        'tier_prices' => []
                    ]
                ),
                'result' => [
                    'sku' => 'MH01',
                    'status' => 1,
                    'product_links' => [],
                    'tier_prices' => []
                ],
                'expectedResult' => [
                    'sku' => 'MH01',
                    'status' => 1,
                    'product_links' => [],
                    'tier_prices' => []
                ]
            ],
            'request object contains `product_links`' => [
                'request' => [
                    [
                        'product' => [
                            'sku' => 'MH01',
                            'status' => 1,
                            'product_links' => []
                        ]
                    ]
                ],
                'product' => static fn (self $testCase) => $testCase->setProductInformation(
                    [
                        'sku' => 'MH01',
                        'status' => 1,
                        'product_links' => [],
                        'tier_prices' => []
                    ]
                ),
                'result' => [
                    'sku' => 'MH01',
                    'status' => 1,
                    'product_links' => [],
                    'tier_prices' => []
                ],
                'expectedResult' => [
                    'sku' => 'MH01',
                    'status' => 1,
                    'product_links' => []
                ]
            ],
            'request object SKU does not match with product object SKU' => [
                'request' => [
                    [
                        'product' => [
                            'sku' => 'MH01',
                            'status' => 1
                        ]
                    ]
                ],
                'product' => static fn (self $testCase) => $testCase->setProductInformation(
                    [
                        'sku' => 'MH03',
                        'status' => 1,
                        'product_links' => [],
                        'tier_prices' => []
                    ]
                ),
                'result' => [
                    'sku' => 'MH01',
                    'status' => 1,
                    'product_links' => [],
                    'tier_prices' => []
                ],
                'expectedResult' => [
                    'sku' => 'MH01',
                    'status' => 1,
                    'product_links' => [],
                    'tier_prices' => []
                ]
            ],
            'request object does not contain `sku`' => [
                'request' => [
                    [
                        'product' => [
                            'status' => 1,
                            'product_links' => [],
                            'tier_prices' => []
                        ]
                    ]
                ],
                'product' => static fn (self $testCase) => $testCase->setProductInformation(
                    [
                        'sku' => 'MH01',
                        'status' => 1,
                        'product_links' => [],
                        'tier_prices' => []
                    ]
                ),
                'result' => [
                    'sku' => 'MH01',
                    'status' => 1,
                    'product_links' => [],
                    'tier_prices' => []
                ],
                'expectedResult' => [
                    'sku' => 'MH01',
                    'status' => 1,
                    'product_links' => [],
                    'tier_prices' => []
                ]
            ],
            'request object has empty product' => [
                'request' => [
                    [
                        'product' => []
                    ]
                ],
                'product' => static fn (self $testCase) => $testCase->setProductInformation(
                    [
                        'sku' => 'MH01',
                        'status' => 1,
                        'product_links' => [],
                        'tier_prices' => []
                    ]
                ),
                'result' => [
                    'sku' => 'MH01',
                    'status' => 1,
                    'product_links' => [],
                    'tier_prices' => []
                ],
                'expectedResult' => [
                    'sku' => 'MH01',
                    'status' => 1,
                    'product_links' => [],
                    'tier_prices' => []
                ]
            ],
            'request object does not contain product' => [
                'request' => [
                    [
                        'order' => [
                            'order_id' => 1,
                            'order_details' => 'test'
                        ]
                    ]
                ],
                'product' => static fn (self $testCase) => $testCase->setProductInformation(
                    [
                        'sku' => 'MH01',
                        'status' => 1,
                        'product_links' => [],
                        'tier_prices' => []
                    ]
                ),
                'result' => [
                    'sku' => 'MH01',
                    'status' => 1,
                    'product_links' => [],
                    'tier_prices' => []
                ],
                'expectedResult' => [
                    'sku' => 'MH01',
                    'status' => 1,
                    'product_links' => [],
                    'tier_prices' => []
                ]
            ],
            'request object contains `product_links` is null and `tier_prices` is null' => [
                'request' => [
                    [
                        'product' => [
                            'sku' => 'MH01',
                            'status' => 1,
                            'product_links' => null,
                            'tier_prices' => null
                        ]
                    ]
                ],
                'product' => static fn (self $testCase) => $testCase->setProductInformation(
                    [
                        'sku' => 'MH01',
                        'status' => 1,
                        'product_links' => [],
                        'tier_prices' => []
                    ]
                ),
                'result' => [
                    'sku' => 'MH01',
                    'status' => 1,
                    'product_links' => null,
                    'tier_prices' => null
                ],
                'expectedResult' => [
                    'sku' => 'MH01',
                    'status' => 1,
                    'product_links' => null,
                    'tier_prices' => null
                ]
            ],
            'request object has empty array' => [
                'request' => [],
                'product' => static fn (self $testCase) => $testCase->setProductInformation(
                    [
                        'sku' => 'MH01',
                        'status' => 1,
                        'product_links' => [],
                        'tier_prices' => []
                    ]
                ),
                'result' => [
                    'sku' => 'MH01',
                    'status' => 1
                ],
                'expectedResult' => [
                    'sku' => 'MH01',
                    'status' => 1
                ]
            ]
        ];
    }

    protected function setProductInformation($productArr)
    {
        $productMock = $this->createStub(ProductInterface::class);
        $productMock
            ->method('getSku')
            ->willReturn($productArr['sku']);
        $productMock
            ->method('getProductLinks')
            ->willReturn($productArr['product_links']);
        $productMock
            ->method('getTierPrices')
            ->willReturn($productArr['tier_prices']);
        return $productMock;
    }
}
