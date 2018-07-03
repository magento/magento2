<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Test\Api\SourceRepository;

use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;

class ValidationTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/sources';
    const SERVICE_NAME = 'inventoryApiSourceRepositoryV1';
    /**#@-*/

    /**
     * @var array
     */
    private $validData = [
        SourceInterface::SOURCE_CODE => 'source-code-1',
        SourceInterface::NAME => 'source-name-1',
        SourceInterface::POSTCODE => 'source-postcode',
        SourceInterface::COUNTRY_ID => 'US',
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
            'without_' . SourceInterface::NAME => [
                SourceInterface::NAME,
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::NAME,
                            ],
                        ],
                    ],
                ],
            ],
            'without_' . SourceInterface::POSTCODE => [
                SourceInterface::POSTCODE,
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::POSTCODE,
                            ],
                        ],
                    ],
                ],
            ],
            'without_' . SourceInterface::SOURCE_CODE => [
                SourceInterface::SOURCE_CODE,
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::SOURCE_CODE,
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

    /**
     * @param string $field
     * @param string|null $value
     * @param array $expectedErrorData
     * @dataProvider failedValidationUpdateDataProvider
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source.php
     */
    public function testFailedValidationOnUpdate(string $field, $value, array $expectedErrorData)
    {
        $data = $this->validData;
        $data[$field] = $value;

        $sourceCode = 'source-code-1';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sourceCode,
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
     * SuppressWarnings was added due to a tests on different fail types and big size of data provider
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function failedValidationDataProvider(): array
    {
        return [
            'null_' . SourceInterface::SOURCE_CODE => [
                SourceInterface::SOURCE_CODE,
                null,
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::SOURCE_CODE,
                            ],
                        ],
                    ],
                ],
            ],
            'empty_' . SourceInterface::SOURCE_CODE => [
                SourceInterface::SOURCE_CODE,
                '',
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::SOURCE_CODE,
                            ],
                        ],
                    ],
                ],
            ],
            'whitespaces_' . SourceInterface::SOURCE_CODE => [
                SourceInterface::SOURCE_CODE,
                ' ',
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::SOURCE_CODE,
                            ],
                        ],
                    ],
                ],
            ],
            'with_whitespaces_' . SourceInterface::SOURCE_CODE => [
                SourceInterface::SOURCE_CODE,
                'source code',
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not contain whitespaces.',
                            'parameters' => [
                                'field' => SourceInterface::SOURCE_CODE,
                            ],
                        ],
                    ],
                ],
            ],
            'null_' . SourceInterface::NAME => [
                SourceInterface::NAME,
                null,
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::NAME,
                            ],
                        ],
                    ],
                ],
            ],
            'empty_' . SourceInterface::NAME => [
                SourceInterface::NAME,
                '',
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::NAME,
                            ],
                        ],
                    ],
                ],
            ],
            'whitespaces_' . SourceInterface::NAME => [
                SourceInterface::NAME,
                ' ',
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::NAME,
                            ],
                        ],
                    ],
                ],
            ],
            'empty_' . SourceInterface::POSTCODE => [
                SourceInterface::POSTCODE,
                '',
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::POSTCODE,
                            ],
                        ],
                    ],
                ],
            ],
            'whitespaces_' . SourceInterface::POSTCODE => [
                SourceInterface::POSTCODE,
                ' ',
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::POSTCODE,
                            ],
                        ],
                    ],
                ],
            ],
            'null_' . SourceInterface::POSTCODE => [
                SourceInterface::POSTCODE,
                null,
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::POSTCODE,
                            ],
                        ],
                    ],
                ],
            ],
            'empty_' . SourceInterface::COUNTRY_ID => [
                SourceInterface::COUNTRY_ID,
                '',
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::COUNTRY_ID,
                            ],
                        ],
                    ],
                ],
            ],
            'whitespaces_' . SourceInterface::COUNTRY_ID => [
                SourceInterface::COUNTRY_ID,
                ' ',
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::COUNTRY_ID,
                            ],
                        ],
                    ],
                ],
            ],
            'null_' . SourceInterface::COUNTRY_ID => [
                SourceInterface::COUNTRY_ID,
                null,
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::COUNTRY_ID,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * SuppressWarnings was added due to a tests on different fail types and big size of data provider.
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function failedValidationUpdateDataProvider(): array
    {
        return [
            'null_' . SourceInterface::NAME => [
                SourceInterface::NAME,
                null,
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::NAME,
                            ],
                        ],
                    ],
                ],
            ],
            'empty_' . SourceInterface::NAME => [
                SourceInterface::NAME,
                '',
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::NAME,
                            ],
                        ],
                    ],
                ],
            ],
            'whitespaces_' . SourceInterface::NAME => [
                SourceInterface::NAME,
                ' ',
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::NAME,
                            ],
                        ],
                    ],
                ],
            ],
            'empty_' . SourceInterface::POSTCODE => [
                SourceInterface::POSTCODE,
                '',
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::POSTCODE,
                            ],
                        ],
                    ],
                ],
            ],
            'whitespaces_' . SourceInterface::POSTCODE => [
                SourceInterface::POSTCODE,
                ' ',
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::POSTCODE,
                            ],
                        ],
                    ],
                ],
            ],
            'null_' . SourceInterface::POSTCODE => [
                SourceInterface::POSTCODE,
                null,
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::POSTCODE,
                            ],
                        ],
                    ],
                ],
            ],
            'empty_' . SourceInterface::COUNTRY_ID => [
                SourceInterface::COUNTRY_ID,
                '',
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::COUNTRY_ID,
                            ],
                        ],
                    ],
                ],
            ],
            'whitespaces_' . SourceInterface::COUNTRY_ID => [
                SourceInterface::COUNTRY_ID,
                ' ',
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::COUNTRY_ID,
                            ],
                        ],
                    ],
                ],
            ],
            'null_' . SourceInterface::COUNTRY_ID => [
                SourceInterface::COUNTRY_ID,
                null,
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => '"%field" can not be empty.',
                            'parameters' => [
                                'field' => SourceInterface::COUNTRY_ID,
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
            $this->_webApiCall($serviceInfo, ['source' => $data]);
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
