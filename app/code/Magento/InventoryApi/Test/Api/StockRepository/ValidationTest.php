<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Test\Api\StockRepository;

use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;

class ValidationTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/stocks';
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
    public function testCreateWithMissedRequiredFields(string $field, array $expectedErrorData)
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
        $this->webApiCall($serviceInfo, $data, $expectedErrorData);
    }

    /**
     * @return array
     */
    public function dataProviderRequiredFields(): array
    {
        return [
            'without_' . StockInterface::NAME => [
                StockInterface::NAME,
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => StockInterface::NAME,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param string $field
     * @param string|null $value
     * @param array $expectedErrorData
     * @dataProvider failedValidationDataProvider
     */
    public function testFailedValidationOnCreate(string $field, $value, array $expectedErrorData)
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

    public function testFailedValidationWhenCreateOnNotExistingWebsite()
    {
        $notExistingWebsiteCode = 'NotExistingWebsite';
        $data = [
            StockInterface::NAME => 'stock-name',
            StockInterface::EXTENSION_ATTRIBUTES_KEY => [
                'sales_channels' => [[
                    'type' => SalesChannelInterface::TYPE_WEBSITE,
                    'code' => $notExistingWebsiteCode
                ]]
            ]
        ];

        $expectedErrorData = [
            'message' => 'Validation Failed',
            'errors' => [
                [
                    'message' => 'The website with code "%code" does not exist.',
                    'parameters' => [
                        'code' => $notExistingWebsiteCode,
                    ],
                ],
            ],
        ];

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
     * @param string|null $value
     * @param array $expectedErrorData
     * @dataProvider failedValidationDataProvider
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock.php
     */
    public function testFailedValidationOnUpdate(string $field, $value, array $expectedErrorData)
    {
        $data = $this->validData;
        $data[$field] = $value;

        $stockId = 10;
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
    public function failedValidationDataProvider(): array
    {
        return [
            'empty_' . StockInterface::NAME => [
                StockInterface::NAME,
                '',
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => StockInterface::NAME,
                            ],
                        ],
                    ],
                ],
            ],
            'whitespaces_' . StockInterface::NAME => [
                StockInterface::NAME,
                ' ',
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => StockInterface::NAME,
                            ],
                        ],
                    ],
                ],
            ],
            'null_' . StockInterface::NAME => [
                StockInterface::NAME,
                null,
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => StockInterface::NAME,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $serviceInfo
     * @param array $data
     * @param array $expectedErrorData
     * @return void
     * @throws \Exception
     */
    private function webApiCall(array $serviceInfo, array $data, array $expectedErrorData)
    {
        try {
            $this->_webApiCall($serviceInfo, ['stock' => $data]);
            $this->fail('Expected throwing exception');
        } catch (\Exception $e) {
            if (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST) {
                self::assertEquals($expectedErrorData, $this->processRestExceptionResult($e));
                self::assertEquals(Exception::HTTP_BAD_REQUEST, $e->getCode());
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
                $this->checkSoapFault($e, $expectedErrorData['message'], 'env:Sender', [], $expectedWrappedErrors);
            } else {
                throw $e;
            }
        }
    }
}
