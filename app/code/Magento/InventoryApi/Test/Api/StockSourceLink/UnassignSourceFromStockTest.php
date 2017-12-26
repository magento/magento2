<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Test\Api\StockSourceLink;

use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;

class UnassignSourceFromStockTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH_GET_ASSIGNED_SOURCES_FOR_STOCK = '/V1/inventory/stock/get-assigned-sources';
    const SERVICE_NAME_GET_ASSIGNED_SOURCES_FOR_STOCK = 'inventoryApiGetAssignedSourcesForStockV1';
    const RESOURCE_PATH_UNASSIGN_SOURCES_FROM_STOCK = '/V1/inventory/stock/unassign-source';
    const SERVICE_NAME_UNASSIGN_SOURCES_FROM_STOCK = 'inventoryApiUnassignSourceFromStockV1';
    /**#@-*/

    /**
     * Preconditions:
     * Sources to Stock links:
     *   EU-source-1(id:10) - EU-stock(id:10)
     *   EU-source-2(id:20) - EU-stock(id:10)
     *   EU-source-3(id:30) - EU-stock(id:10)
     *   EU-source-disabled(id:40) - EU-stock(id:10)
     *
     * Test case:
     *   Unassign EU-source-1(id:10) from EU-stock(id:10)
     *
     * Expected data:
     *   EU-source-2(id:20) - EU-stock(id:10)
     *   EU-source-3(id:30) - EU-stock(id:10)
     *   EU-source-disabled(id:40) - EU-stock(id:10)
     *
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testUnassignSourceFromStock()
    {
        $sourceCode = 'eu-1';
        $stockId = 10;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_UNASSIGN_SOURCES_FROM_STOCK . '/' . $stockId . '/' . $sourceCode,
                'httpMethod' => Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME_UNASSIGN_SOURCES_FROM_STOCK,
                'operation' => self::SERVICE_NAME_UNASSIGN_SOURCES_FROM_STOCK . 'Execute',
            ],
        ];
        (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['sourceCode' => $sourceCode, 'stockId' => $stockId]);

        $assignedSourcesForStock = $this->getAssignedSourcesForStock($stockId);
        self::assertEquals(
            ['eu-2', 'eu-3', 'eu-disabled'],
            array_column($assignedSourcesForStock, SourceInterface::SOURCE_CODE)
        );
    }

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock.php
     * @param string|string $sourceCode
     * @param string|int $stockId
     * @param array $expectedErrorData
     * @throws \Exception
     * @dataProvider dataProviderWrongParameters
     */
    public function testUnassignSourceFromStockWithWrongParameters($sourceCode, $stockId, array $expectedErrorData)
    {
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $this->markTestSkipped(
                'Test works only for REST adapter because in SOAP one source_code/stock_id would be converted'
                . ' into zero (zero is allowed input for service ner mind it\'s illigible value as'
                . ' there are no Sources(Stocks) in the system with source_code/stock_id given)'
            );
        }
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_UNASSIGN_SOURCES_FROM_STOCK . '/' . $stockId . '/' . $sourceCode,
                'httpMethod' => Request::HTTP_METHOD_DELETE,
            ],
        ];
        try {
            $this->_webApiCall($serviceInfo);
            $this->fail('Expected throwing exception');
        } catch (\Exception $e) {
            self::assertEquals($expectedErrorData, $this->processRestExceptionResult($e));
            self::assertEquals(Exception::HTTP_BAD_REQUEST, $e->getCode());
        }
    }

    /**
     * @return array
     */
    public function dataProviderWrongParameters(): array
    {
        return [
            'not_numeric_stock_id' => [
                'eu-1',
                'not_numeric',
                [
                    'message' => 'Invalid type for value: "not_numeric". Expected Type: "int".',
                ],
            ],
            'not_string_source_code' => [
                10,
                [],
                [
                    'message' => 'Invalid type for value: "10". Expected Type: "string".',
                ],
            ],
        ];
    }

    /**
     * @param int $stockId
     * @return array
     */
    private function getAssignedSourcesForStock(int $stockId): array
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_GET_ASSIGNED_SOURCES_FOR_STOCK . '/' . $stockId,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME_GET_ASSIGNED_SOURCES_FOR_STOCK,
                'operation' => self::SERVICE_NAME_GET_ASSIGNED_SOURCES_FOR_STOCK . 'Execute',
            ],
        ];
        $response = (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['stockId' => $stockId]);
        return $response;
    }
}
