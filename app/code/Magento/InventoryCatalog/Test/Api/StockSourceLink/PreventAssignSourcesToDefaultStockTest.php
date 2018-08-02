<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Api\StockSourceLink;

use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Soap\Fault;

class PreventAssignSourcesToDefaultStockTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH_ASSIGN_SOURCES_TO_STOCK = '/V1/inventory/stock-source-links';
    const SERVICE_NAME_ASSIGN_SOURCES_TO_STOCK = 'inventoryApiStockSourceLinksSaveV1';
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
                'resourcePath' => self::RESOURCE_PATH_ASSIGN_SOURCES_TO_STOCK,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME_ASSIGN_SOURCES_TO_STOCK,
                'operation' => self::SERVICE_NAME_ASSIGN_SOURCES_TO_STOCK . 'Execute',
            ],
        ];

        $links = [];
        foreach ($sourceCodes as $sourceCode) {
            $links['links'][] = ['stock_id' => $stockId, 'source_code' => $sourceCode, 'priority' => 1];
        }
        try {
            (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
                ? $this->_webApiCall($serviceInfo, $links)
                : $this->_webApiCall($serviceInfo, $links);
            $this->fail('Expected throwing exception');
        } catch (\Exception $e) {
            if (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST) {
                self::assertEquals($expectedErrorData, $this->processRestExceptionResult($e));
                self::assertEquals(\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST, $e->getCode());
            } elseif (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
                $this->assertInstanceOf('SoapFault', $e);
                $expectedWrappedErrors = [];
                foreach ($expectedErrorData['errors'] as $error) {
                    // @see \Magento\TestFramework\TestCase\WebapiAbstract::getActualWrappedErrors()
                    $expectedWrappedErrors[] = [
                        'message' => $error['message'],
                        'params' => $error['parameters'],
                    ];
                }
                $this->checkSoapFault(
                    $e,
                    $expectedErrorData['message'],
                    'env:Sender',
                    [],
                    $expectedWrappedErrors
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
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => 'Can not save link related to Default Source or Default Stock',
                            'parameters' => [],
                        ],
                    ],
                ],
            ],
            'not_default_source_assigned_to_default_stock' => [
                ['eu-1'],
                $defaultStockProvider->getId(),
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => 'Can not save link related to Default Source or Default Stock',
                            'parameters' => [],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function _checkWrappedErrors($expectedWrappedErrors, $errorDetails)
    {
        $expectedErrors = [];
        $wrappedErrorsNode = Fault::NODE_DETAIL_WRAPPED_ERRORS;
        $wrappedErrorNode = Fault::NODE_DETAIL_WRAPPED_ERROR;
        foreach ($expectedWrappedErrors as $expectedError) {
            $expectedErrors[] = $expectedError['message'];
        }
        $actualErrors = [];
        foreach ($errorDetails->$wrappedErrorsNode->$wrappedErrorNode as $error) {
            if (is_object($error)) {
                $actualErrors[] = $error->message;
            } else {
                $actualErrors[] = $error;
            }
        }
        $this->assertEquals(
            $expectedErrors,
            $actualErrors,
            'Wrapped errors in fault details are invalid.'
        );
    }
}
