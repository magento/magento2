<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStockApi\Test\Api;

use Magento\Framework\Api\SearchCriteria;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Rest\Request;

class ExportStockSalableQtyTest extends WebapiAbstract
{
    const API_PATH = '/V1/inventory/export-stock-salable-qty';
    const SERVICE_NAME = 'inventoryExportStockApiExportStockSalableQtyV1';

    /**
     * @return array
     */
    public function executeDataProvider(): array
    {
        return [
            ['SKU-4', 'website', 'base', ['sku' => 'SKU-4', 'qty' => 0, 'is_salable' => true]]
        ];
    }

    /**
     * @param string $sku
     * @param string $type
     * @param string $code
     * @param array $expected
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @dataProvider executeDataProvider
     * @magentoDbIsolation disabled
     */
    public function testExportStockSalableQty(string $sku, string $type, string $code, array $expected): void
    {
        $this->_markTestAsRestOnly("https://github.com/magento-engcom/msi/issues/2314");
        $requestData = [
            'searchCriteria' => [
                SearchCriteria::FILTER_GROUPS => [
                    [
                        'filters' => [
                            [
                                'field' => SourceItemInterface::SKU,
                                'value' => $sku,
                                'condition_type' => 'eq'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf("%s/%s/%s?%s", self::API_PATH, $type, $code, http_build_query($requestData)),
                'httpMethod' => Request::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute'
            ]
        ];

        $res = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, $requestData);

        self::assertEquals($expected, current($res['items']));
    }
}
