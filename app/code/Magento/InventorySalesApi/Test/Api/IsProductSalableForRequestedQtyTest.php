<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Test\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Rest\Request;

class IsProductSalableForRequestedQtyTest extends WebapiAbstract
{
    const API_PATH = '/V1/inventory/is-product-salable-for-requested-qty';
    const SERVICE_NAME = 'inventorySalesApiIsProductSalableForRequestedQtyV1';

    /**
     * @return array
     */
    public function executeDataProvider(): array
    {
        return [
            ['SKU-1', 10, 1, true],
            ['SKU-1', 20, 1, false],
            ['SKU-1', 30, 1, true],
        ];
    }

    /**
     * @param string $sku
     * @param int $stockId
     * @param float $requestedQty
     * @param bool $expectedResult
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoConfigFixture default_store cataloginventory/item_options/manage_stock 0
     * @dataProvider executeDataProvider
     *
     * @magentoDbIsolation disabled
     */
    public function testDeleteSourceItemConfiguration(
        string $sku,
        int $stockId,
        float $requestedQty,
        bool $expectedResult
    ) {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::API_PATH . '/' . $sku . '/' . $stockId . '/' . $requestedQty,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
        ];

        $res = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, [
                'sku' => $sku,
                'stockId' => $stockId,
                'requestedQty' => $requestedQty
            ]);

        self::assertEquals($expectedResult, $res['salable']);
    }
}
