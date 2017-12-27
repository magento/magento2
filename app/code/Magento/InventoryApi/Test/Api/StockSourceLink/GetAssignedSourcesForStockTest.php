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

class GetAssignedSourcesForStockTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH_GET_ASSIGNED_SOURCES_FOR_STOCK = '/V1/inventory/stock/get-assigned-sources';
    const SERVICE_NAME_GET_ASSIGNED_SOURCES_FOR_STOCK = 'inventoryApiGetAssignedSourcesForStockV1';
    /**#@-*/

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testGetAssignedSourcesForStock()
    {
        $stockId = 10;
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
        self::assertEquals(
            ['eu-1', 'eu-2', 'eu-3', 'eu-disabled'],
            array_column($response, SourceInterface::SOURCE_CODE)
        );
    }

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testGetAssignedSourcesWithNotNumericStockId()
    {
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $this->markTestSkipped(
                'Test works only for REST adapter because in SOAP one stock_id would be converted'
                . ' into zero (zero is allowed input for service ner mind it\'s illigible value as'
                . ' there are no Stocks in the system with stock_id given)'
            );
        }
        $stockId = 'not_numeric';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_GET_ASSIGNED_SOURCES_FOR_STOCK . '/' . $stockId,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
        ];
        try {
            $this->_webApiCall($serviceInfo);
            $this->fail('Expected throwing exception');
        } catch (\Exception $e) {
            $errorData = $this->processRestExceptionResult($e);
            self::assertEquals('Invalid type for value: "not_numeric". Expected Type: "int".', $errorData['message']);
            self::assertEquals(Exception::HTTP_BAD_REQUEST, $e->getCode());
        }
    }
}
