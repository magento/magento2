<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStockApi\Test\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Rest\Request;

class ExportStockIndexDataTest extends WebapiAbstract
{
    const API_PATH = '/V1/inventory/dump-stock-index-data';
    const SERVICE_NAME = 'inventoryExportStockApiExportStockIndexDataV1';

    const EXPORT_PRODUCT_COUNT = 6;

    /**
     * @return array
     */
    public function executeDataProvider(): array
    {
        return [
            ['website', 'base', self::EXPORT_PRODUCT_COUNT]
        ];
    }

    /**
     * @param string $type
     * @param string $code
     * @param int $expectedResult
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @dataProvider       executeDataProvider
     * @magentoDbIsolation disabled
     */
    public function testExportStockData(string $type, string $code, int $expectedResult): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::API_PATH . '/' . $type . '/' . $code,
                'httpMethod' => Request::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute'
            ]
        ];

        $res = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['salesChannelCode' => $code]);

        self::assertEquals($expectedResult, count($res));
    }
}
