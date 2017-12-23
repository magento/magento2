<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Test\Api\StockSourceLink;

use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Inventory\Model\ResourceModel\Source as SourceResourceModel;
use Magento\TestFramework\TestCase\WebapiAbstract;

class AssignSourcesToStockTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH_GET_ASSIGNED_SOURCES_FOR_STOCK = '/V1/inventory/stock/get-assigned-sources';
    const SERVICE_NAME_GET_ASSIGNED_SOURCES_FOR_STOCK = 'inventoryApiGetAssignedSourcesForStockV1';
    const RESOURCE_PATH_ASSIGN_SOURCES_TO_STOCK = '/V1/inventory/stock/assign-sources';
    const SERVICE_NAME_ASSIGN_SOURCES_TO_STOCK = 'inventoryApiAssignSourcesToStockV1';
    /**#@-*/

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock.php
     */
    public function testAssignSourcesToStock()
    {
        $sourceIds = [10, 20];
        $stockId = 10;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_ASSIGN_SOURCES_TO_STOCK . '/' . $stockId,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME_ASSIGN_SOURCES_TO_STOCK,
                'operation' => self::SERVICE_NAME_ASSIGN_SOURCES_TO_STOCK . 'Execute',
            ],
        ];
        (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo, ['sourceIds' => $sourceIds])
            : $this->_webApiCall($serviceInfo, ['sourceIds' => $sourceIds, 'stockId' => $stockId]);

        $assignedSourcesForStock = $this->getAssignedSourcesForStock($stockId);
        self::assertEquals($sourceIds, array_column($assignedSourcesForStock, SourceResourceModel::SOURCE_ID_FIELD));
    }

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock.php
     * @param string|array $sourceIds
     * @param string|int $stockId
     * @param array $expectedErrorData
     * @throws \Exception
     * @dataProvider dataProviderWrongParameters
     */
    public function testAssignSourcesToStockWithWrongParameters($sourceIds, $stockId, array $expectedErrorData)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_ASSIGN_SOURCES_TO_STOCK . '/' . $stockId,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME_ASSIGN_SOURCES_TO_STOCK,
                'operation' => self::SERVICE_NAME_ASSIGN_SOURCES_TO_STOCK . 'Execute',
            ],
        ];
        try {
            (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST)
                ? $this->_webApiCall($serviceInfo, ['sourceIds' => $sourceIds])
                : $this->_webApiCall($serviceInfo, ['sourceIds' => $sourceIds, 'stockId' => $stockId]);
            $this->fail('Expected throwing exception');
        } catch (\Exception $e) {
            if (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST) {
                $errorData = $this->processRestExceptionResult($e);
                self::assertEquals($expectedErrorData['rest_message'], $errorData['message']);
                self::assertEquals(Exception::HTTP_BAD_REQUEST, $e->getCode());
            } elseif (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
                $this->assertInstanceOf('SoapFault', $e);
                $this->checkSoapFault(
                    $e,
                    $expectedErrorData['soap_message'],
                    'env:Sender'
                );
            } else {
                throw $e;
            }
        }
    }

    /**
     * @return array
     */
    public function dataProviderWrongParameters(): array
    {
        return [
            'not_numeric_stock_id' => [
                [10, 20],
                'not_numeric',
                [
                    'rest_message' => 'Invalid type for value: "not_numeric". Expected Type: "int".',
                    // During SOAP stock_id parameter will be converted to zero so error is different
                    'soap_message' => 'Could not assign Sources to Stock',
                ],
            ],
            'nonexistent_stock_id' => [
                [10, 20],
                -1,
                [
                    'rest_message' => 'Could not assign Sources to Stock',
                    'soap_message' => 'Could not assign Sources to Stock',
                ],
            ],
            'not_array_source_ids' => [
                'not_array',
                10,
                [
                    'rest_message' => 'Invalid type for value: "string". Expected Type: "int[]".',
                    // During SOAP source_ids parameter will be converted to empty array so error is different
                    'soap_message' => 'Input data is invalid',
                ],
            ],
            'empty_source_ids' => [
                [],
                10,
                [
                    'rest_message' => 'Input data is invalid',
                    'soap_message' => 'Input data is invalid',
                ],
            ],
            'nonexistent_source_id' => [
                [-1, 20],
                10,
                [
                    'rest_message' => 'Could not assign Sources to Stock',
                    'soap_message' => 'Could not assign Sources to Stock',
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
