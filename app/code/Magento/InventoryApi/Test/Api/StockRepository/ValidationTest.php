<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Test\Api\StockRepository;

use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;

class ValidationTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/stock';
    const SERVICE_NAME = 'inventoryApiStockRepositoryV1';
    /**#@-*/

    /**
     * @var array
     */
    private $validData = [
        StockInterface::NAME => 'stock-name',
    ];

    /**
     * @param string $field
     * @param array $expectedErrorData
     * @throws \Exception
     * @dataProvider dataProviderRequiredFields
     */
    public function testCreateWithMissedRequiredFields($field, array $expectedErrorData)
    {
        $data = $this->validData;
        unset($data[$field]);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        try {
            $this->_webApiCall($serviceInfo, ['stock' => $data]);
            $this->fail('Expected throwing exception');
        } catch (\Exception $e) {
            if (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST) {
                $errorData = $this->processRestExceptionResult($e);
                self::assertEquals($expectedErrorData, $errorData);
                self::assertEquals(Exception::HTTP_BAD_REQUEST, $e->getCode());
            } elseif (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
                $this->assertInstanceOf('SoapFault', $e);
                $this->checkSoapFault(
                    $e,
                    $expectedErrorData['soap_message'],
                    'Sender'
                );
            } else {
                throw $e;
            }
        }
    }

    /**
     * @return array
     */
    public function dataProviderRequiredFields()
    {
        return [
            'without_' . StockInterface::NAME => [
                StockInterface::NAME,
                [
                    'message' => '"%1" can not be empty.',
                    'parameters' => [
                        StockInterface::NAME,
                    ],
                    'soap_message' => sprintf('object has no \'%s\' property', StockInterface::NAME),
                ],
            ],
        ];
    }

    /**
     * @param string $field
     * @param string $value
     * @param array $expectedErrorData
     * @dataProvider failedValidationDataProvider
     */
    public function testFailedValidationOnCreate($field, $value, array $expectedErrorData)
    {
        $data = $this->validData;
        $data[$field] = $value;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $this->webApiCall($serviceInfo, $data, $expectedErrorData);
    }

    /**
     * @param string $field
     * @param string $value
     * @param array $expectedErrorData
     * @dataProvider failedValidationDataProvider
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock/stock.php
     */
    public function testFailedValidationOnUpdate($field, $value, array $expectedErrorData)
    {
        $data = $this->validData;
        $data[$field] = $value;

        $stock = $this->getStockDataByName('stock-name-1');
        $stockId = $stock[StockInterface::STOCK_ID];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $stockId,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $this->webApiCall($serviceInfo, $data, $expectedErrorData);
    }

    /**
     * @return array
     */
    public function failedValidationDataProvider()
    {
        return [
            'empty_' . StockInterface::NAME => [
                StockInterface::NAME,
                null,
                [
                    'message' => '"%1" can not be empty.',
                    'parameters' => [
                        1 => StockInterface::NAME,
                    ],
                ],
            ],
            'whitespaces_' . StockInterface::NAME => [
                StockInterface::NAME,
                ' ',
                [
                    'message' => '"%1" can not be empty.',
                    'parameters' => [
                        1 => StockInterface::NAME,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $serviceInfo
     * @param array $data
     * @param array $expectedErrorData
     * @throws \Exception
     */
    private function webApiCall(array $serviceInfo, array $data, array $expectedErrorData)
    {
        try {
            $this->_webApiCall($serviceInfo, ['stock' => $data]);
            $this->fail('Expected throwing exception');
        } catch (\Exception $e) {
            if (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST) {
                $errorData = $this->processRestExceptionResult($e);
                self::assertEquals($expectedErrorData, $errorData);
                self::assertEquals(Exception::HTTP_BAD_REQUEST, $e->getCode());
            } elseif (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
                $this->assertInstanceOf('SoapFault', $e);
                $this->checkSoapFault(
                    $e,
                    $expectedErrorData['message'],
                    'env:Sender',
                    $expectedErrorData['parameters']
                );
            } else {
                throw $e;
            }
        }
    }

    /**
     * TODO: duplication
     * @param string $name
     * @return array
     */
    private function getStockDataByName($name)
    {
        $searchCriteria = [
            'filter_groups' => [
                [
                    'filters' => [
                        [
                            'field' => StockInterface::NAME,
                            'value' => $name,
                            'condition_type' => 'eq',
                        ],
                    ],
                ],
                'page_size' => 1,
            ],
        ];
        $requestData = ['searchCriteria' => $searchCriteria];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($requestData),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];
        $response = (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, $requestData);

        self::assertArrayHasKey('items', $response);
        self::assertCount(1, $response['items']);
        return reset($response['items']);
    }
}
