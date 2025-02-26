<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Webapi;

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
        $this->requestMock = $this->createPartialMock(
            Request::class,
            ['getContent']
        );
        $this->deserializerMock = $this->getMockBuilder(DeserializerInterface::class)
            ->getMockForAbstractClass();
        $this->productOutputProcessor = new ProductOutputProcessor($this->requestMock, $this->deserializerMock);
    }

    /**
     * @dataProvider getProductProcessorDataProvider
     * @param $request
     * @param $product
     * @param $result
     * @param $expectedResult
     */
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
            ->willReturn($request);
        $this->deserializerMock
            ->method('deserialize')
            ->willReturn($request);
        $this->assertEquals($expectedResult, $this->productOutputProcessor->execute($product, $result));
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
        $productMock = $this->getMockBuilder(ProductInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'setSku',
                    'setStatus',
                    'setProductLinks',
                    'setTierPrices',
                    'getSku',
                    'getProductLinks',
                    'getTierPrices'
                ]
            )
            ->getMockForAbstractClass();
        $productMock
            ->method('setSku')
            ->with($productArr['sku'])
            ->willReturn(true);
        $productMock
            ->method('getSku')
            ->willReturn($productArr['sku']);
        $productMock
            ->method('setStatus')
            ->with($productArr['status'])
            ->willReturn(true);
        $productMock
            ->method('setProductLinks')
            ->with($productArr['product_links'])
            ->willReturn(true);
        $productMock
            ->method('getProductLinks')
            ->willReturn($productArr['product_links']);
        $productMock
            ->method('setTierPrices')
            ->with($productArr['tier_prices'])
            ->willReturn(true);
        $productMock
            ->method('getTierPrices')
            ->willReturn($productArr['tier_prices']);
        return $productMock;
    }
}
