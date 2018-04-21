<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Api\StockSourceLink;

use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class PreventAssignSourcesToDefaultStockTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH_ASSIGN_SOURCES_TO_STOCK = '/V1/inventory/stock/assign-sources';
    const SERVICE_NAME_ASSIGN_SOURCES_TO_STOCK = 'inventoryApiAssignSourcesToStockV1';
    /**#@-*/

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock.php
     * @param array $sourceCodes
     * @param int $stockId
     * @param array $expectedErrorData
     * @throws \Exception
     * @dataProvider dataProviderWrongParameters
     */
    public function testAssignSourcesToStockWithWrongParameters(
        array $sourceCodes,
        int $stockId,
        array $expectedErrorData
    ) {
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
                ? $this->_webApiCall($serviceInfo, ['sourceCodes' => $sourceCodes])
                : $this->_webApiCall($serviceInfo, ['sourceCodes' => $sourceCodes, 'stockId' => $stockId]);
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
        $defaultSourceProvider = Bootstrap::getObjectManager()->get(DefaultSourceProviderInterface::class);
        $defaultStockProvider = Bootstrap::getObjectManager()->get(DefaultStockProviderInterface::class);

        return [
            'multiple_sources_assigned_to_default_stock' => [
                [$defaultSourceProvider->getCode(), 'eu-2'],
                $defaultStockProvider->getId(),
                [
                    'rest_message' => 'You can only assign Default Source to Default Stock',
                    'soap_message' => 'You can only assign Default Source to Default Stock',
                ],
            ],
            'not_default_source_assigned_to_default_stock' => [
                ['eu-1'],
                $defaultStockProvider->getId(),
                [
                    'rest_message' => 'You can only assign Default Source to Default Stock',
                    'soap_message' => 'You can only assign Default Source to Default Stock',
                ],
            ],
        ];
    }
}
