<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryApi\Test\Api;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Webapi\Exception;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\TestFramework\Assert\AssertArrayContains;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * @covers \Magento\InventoryApi\Api\SourceItemsSaveInterface
 */
class SourceItemsSaveTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/source-items';
    const SERVICE_NAME = 'inventoryApiSourceItemsSaveV1';
    /**#@-*/

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @covers \Magento\InventoryApi\Api\SourceItemsSaveInterface::execute
     */
    public function testExecute()
    {

        $expectedItemsData = [
            [
                SourceItemInterface::SOURCE_ID => 10,
                SourceItemInterface::SKU => 'SKU-1',
                SourceItemInterface::QUANTITY => 5.5,
                SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
            ],
            [
                SourceItemInterface::SOURCE_ID => 20,
                SourceItemInterface::SKU => 'SKU-1',
                SourceItemInterface::QUANTITY => 3,
                SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
            ],
        ];

        $expectedTotalCount = 2;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'execute',
            ],
        ];

        $this->_webApiCall($serviceInfo, [ 'sourceItems' => $expectedItemsData, ]);

        $actualData = $this->getAssertionResults();

        self::assertEquals($expectedTotalCount, $actualData['total_count']);
        AssertArrayContains::assert($expectedItemsData, $actualData['items']);
    }

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @covers \Magento\InventoryApi\Api\SourceItemsSaveInterface::execute
     */
    public function testExecuteWithEmptyData()
    {
        $this->callWebApi(
            [ 'sourceItems' => []]
            , 'Input data is empty'
        );
    }

    /**
     * @covers \Magento\InventoryApi\Api\SourceItemsSaveInterface::execute
     */
    public function testExecuteWithIncorrectData()
    {
        // no source exists
        $this->callWebApi(
            [ 'sourceItems' => [[
                SourceItemInterface::SOURCE_ID => 10,
                SourceItemInterface::SKU => 'SKU-1',
                SourceItemInterface::QUANTITY => 5.5,
                SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
            ]]]
            , 'Could not save Source Item'
        );
    }

    /**
     * @return array
     */
    private function getAssertionResults(): array
    {
        $requestData = [
            'searchCriteria' => [
                SearchCriteria::FILTER_GROUPS => [
                    [
                        'filters' => [
                            [
                                'field' => SourceItemInterface::SKU,
                                'value' => 'SKU-1',
                                'condition_type' => 'eq',
                            ],
                        ],
                    ],
                ],
                SearchCriteria::SORT_ORDERS => [
                    [
                        'field' => SourceItemInterface::QUANTITY,
                        'direction' => SortOrder::SORT_DESC,
                    ],
                ]
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/inventory/source-item?' . http_build_query($requestData),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'inventoryApiSourceItemRepositoryV1',
                'operation' => 'inventoryApiSourceItemRepositoryV1GetList',
            ],
        ];
        return (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * @param array $requestData
     * @param string $expectedMessage
     * @throws \Exception
     */
    private function callWebApi(array $requestData, string $expectedMessage)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'execute',
            ],
        ];

        try {
            $this->_webApiCall($serviceInfo, $requestData);

            $this->fail('Expected throwing exception');
        } catch (\Exception $exception) {
            if (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST) {
                $errorData = $this->processRestExceptionResult($exception);
                self::assertEquals($expectedMessage, $errorData['message']);
                self::assertEquals(Exception::HTTP_BAD_REQUEST, $exception->getCode());
            } elseif (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
                $this->assertInstanceOf('SoapFault', $exception);
                $this->checkSoapFault($exception, $expectedMessage, 'env:Sender');
            } else {
                throw $exception;
            }
        }
    }
}
