<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Api;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Products returned by the product list carry the same stock item as a single product request
 */
class ProductRepositoryGetListStockItemTest extends WebapiAbstract
{
    private const SERVICE_NAME = 'catalogProductRepositoryV1';
    private const SERVICE_VERSION = 'V1';
    private const RESOURCE_PATH = '/V1/products';

    #[
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'stock-item-list-1',
                'name' => 'Stock Item List',
                'extension_attributes' => ['stock_item' => ['qty' => 41]],
            ]
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'stock-item-list-2',
                'name' => 'Stock Item List',
                'extension_attributes' => ['stock_item' => ['qty' => 42]],
            ]
        ),
    ]
    public function testGetListFilteredByAttributeReturnsStockItem(): void
    {
        $searchCriteria = [
            'searchCriteria' => [
                'filter_groups' => [
                    [
                        'filters' => [
                            ['field' => 'name', 'value' => 'Stock Item List', 'condition_type' => 'eq'],
                        ],
                    ],
                ],
            ],
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($searchCriteria),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, $searchCriteria);

        $this->assertCount(2, $response['items']);
        $quantities = [];
        foreach ($response['items'] as $item) {
            $this->assertArrayHasKey('stock_item', $item['extension_attributes']);
            $quantities[$item['sku']] = (float)$item['extension_attributes']['stock_item']['qty'];
        }
        ksort($quantities);
        $this->assertEquals(['stock-item-list-1' => 41.0, 'stock-item-list-2' => 42.0], $quantities);
    }
}
