<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Test\Api\SourceItemsSave;

use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;

class ValidationTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/source-items';
    const SERVICE_NAME = 'inventoryApiSourceItemsSaveV1';
    /**#@-*/

    /**
     * @var array
     */
    private $validData = [
        SourceItemInterface::SKU => 'SKU-1',
        SourceItemInterface::QUANTITY => 1.5,
        SourceItemInterface::SOURCE_CODE => 'eu-1',
        SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
    ];

    /**
     * @param string $field
     * @param array $expectedErrorData
     * @throws \Exception
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
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
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
        ];
        $this->webApiCall($serviceInfo, [$data], $expectedErrorData);
    }

    /**
     * @return array
     */
    public function dataProviderRequiredFields(): array
    {
        return [
            'without_' . SourceItemInterface::SKU => [
                SourceItemInterface::SKU,
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceItemInterface::SKU,
                            ],
                        ],
                    ],
                ],
            ],
            'without_' . SourceItemInterface::SOURCE_CODE => [
                SourceItemInterface::SOURCE_CODE,
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceItemInterface::SOURCE_CODE,
                            ],
                        ],
                    ],
                ],
            ],
            'without_' . SourceItemInterface::QUANTITY => [
                SourceItemInterface::QUANTITY,
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" should be numeric.',
                            'parameters' => [
                                'field' => SourceItemInterface::QUANTITY,
                            ],
                        ],
                    ],
                ],
            ],
            'without_' . SourceItemInterface::STATUS => [
                SourceItemInterface::STATUS,
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" should be numeric.',
                            'parameters' => [
                                'field' => SourceItemInterface::STATUS,
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
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
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
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
        ];
        $this->webApiCall($serviceInfo, [$data], $expectedErrorData);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function failedValidationDataProvider(): array
    {
        if (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
            $nonExistedSourceCodeError = [
                'message' => 'Could not save Source Item',
                'errors' => [],
            ];
        } else {
            $nonExistedSourceCodeError = ['message' => 'Could not save Source Item'];
        }

        return [
            'null_' . SourceItemInterface::SKU => [
                SourceItemInterface::SKU,
                null,
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceItemInterface::SKU,
                            ],
                        ],
                    ],
                ],
            ],
            'empty_' . SourceItemInterface::SKU => [
                SourceItemInterface::SKU,
                '',
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceItemInterface::SKU,
                            ],
                        ],
                    ],
                ],
            ],
            'whitespaces_' . SourceItemInterface::SKU => [
                SourceItemInterface::SKU,
                ' ',
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceItemInterface::SKU,
                            ],
                        ],
                    ],
                ],
            ],
            'unknown_' . SourceItemInterface::STATUS => [
                SourceItemInterface::STATUS,
                '999999',
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" should a known status.',
                            'parameters' => [
                                'field' => SourceItemInterface::STATUS,
                            ],
                        ],
                    ],
                ],
            ],
            'null_' . SourceItemInterface::QUANTITY => [
                SourceItemInterface::QUANTITY,
                null,
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" should be numeric.',
                            'parameters' => [
                                'field' => SourceItemInterface::QUANTITY,
                            ],
                        ],
                    ],
                ],
            ],
            'null_' . SourceItemInterface::SOURCE_CODE => [
                SourceItemInterface::SOURCE_CODE,
                null,
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceItemInterface::SOURCE_CODE,
                            ],
                        ],
                    ],
                ],
            ],
            'empty_' . SourceItemInterface::SOURCE_CODE => [
                SourceItemInterface::SOURCE_CODE,
                '',
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceItemInterface::SOURCE_CODE,
                            ],
                        ],
                    ],
                ],
            ],
            'whitespaces_' . SourceItemInterface::SOURCE_CODE => [
                SourceItemInterface::SOURCE_CODE,
                ' ',
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceItemInterface::SOURCE_CODE,
                            ],
                        ],
                    ],
                ],
            ],
            'not_exists_' . SourceItemInterface::SOURCE_CODE => [
                SourceItemInterface::SOURCE_CODE,
                'not-existed-source-code',
                $nonExistedSourceCodeError,
            ],
            'with_whitespaces_' . SourceItemInterface::SOURCE_CODE => [
                SourceItemInterface::SOURCE_CODE,
                'source code',
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not contain whitespaces.',
                            'parameters' => [
                                'field' => SourceItemInterface::SOURCE_CODE,
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
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @dataProvider failedValidationRelatedOnlyForRestDataProvider
     */
    public function testFailedValidationOnCreateRelatedOnlyForRest(string $field, $value, array $expectedErrorData)
    {
        if (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
            $this->markTestSkipped(
                'Test works only for REST adapter because in SOAP one parameters would be converted'
                . ' into zero (zero is allowed input value)'
            );
        }

        $data = $this->validData;
        $data[$field] = $value;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
        ];
        $this->webApiCall($serviceInfo, [$data], $expectedErrorData);
    }

    /**
     * @return array
     */
    public function failedValidationRelatedOnlyForRestDataProvider(): array
    {
        return [
            'empty_' . SourceItemInterface::QUANTITY => [
                SourceItemInterface::QUANTITY,
                '',
                [
                    'message' => 'Error occurred during "' . SourceItemInterface::QUANTITY
                        . '" processing. The "" value\'s type is invalid. The "float" type was expected. '
                        . 'Verify and try again.',
                ],
            ],
            'string_' . SourceItemInterface::QUANTITY => [
                SourceItemInterface::QUANTITY,
                'test',
                [
                    'message' => 'Error occurred during "' . SourceItemInterface::QUANTITY
                        . '" processing. The "test" value\'s type is invalid. The "float" type was expected. '
                        . 'Verify and try again.',
                ],
            ],
            'array_' . SourceItemInterface::SOURCE_CODE => [
                SourceItemInterface::SOURCE_CODE,
                [],
                [
                    'message' => 'Error occurred during "' . SourceItemInterface::SOURCE_CODE
                        . '" processing. The "array" value\'s type is invalid. The "string" type was expected. '
                        . 'Verify and try again.',
                ],
            ],
        ];
    }

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     */
    public function testCreateWithEmptyData()
    {
        $sourceItems = [];
        if (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
            $expectedErrorData = [
                'message' => 'Input data is empty',
                'errors' => [],
            ];
        } else {
            $expectedErrorData = ['message' => 'Input data is empty'];
        }

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
        ];
        $this->webApiCall($serviceInfo, $sourceItems, $expectedErrorData);
    }

    /**
     * @param array $serviceInfo
     * @param array $sourceItems
     * @param array $expectedErrorData
     * @return void
     * @throws \Exception
     */
    private function webApiCall(array $serviceInfo, array $sourceItems, array $expectedErrorData)
    {
        try {
            $this->_webApiCall($serviceInfo, ['sourceItems' => $sourceItems]);
            $this->fail('Expected throwing exception');
        } catch (\Exception $exception) {
            if (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST) {
                self::assertEquals($expectedErrorData, $this->processRestExceptionResult($exception));
                self::assertEquals(Exception::HTTP_BAD_REQUEST, $exception->getCode());
            } elseif (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
                $this->assertInstanceOf('SoapFault', $exception);
                $expectedWrappedErrors = [];
                foreach ($expectedErrorData['errors'] as $error) {
                    // @see \Magento\TestFramework\TestCase\WebapiAbstract::getActualWrappedErrors()
                    $expectedWrappedErrors[] = [
                        'message' => $error['message'],
                        'params' => $error['parameters'],
                    ];
                }
                $this->checkSoapFault(
                    $exception,
                    $expectedErrorData['message'],
                    'env:Sender',
                    [],
                    $expectedWrappedErrors
                );
            } else {
                throw $exception;
            }
        }
    }
}
