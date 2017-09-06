<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
    const RESOURCE_PATH = '/V1/inventory/source';
    const SERVICE_NAME = 'inventoryApiSourceRepositoryV1';
    /**#@-*/

    /**
     * @var array
     */
    private $validData = [
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
            $this->_webApiCall($serviceInfo, ['source' => $data]);
            $this->fail('Expected throwing exception');
        } catch (\Exception $e) {
            if (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST) {
                $errorData = $this->processRestExceptionResult($e);
                self::assertEquals($expectedErrorData['rest_message'], $errorData['message']);
                self::assertEquals($expectedErrorData['parameters'], $errorData['parameters']);
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
            'without_' . SourceInterface::NAME => [
                SourceInterface::NAME,
                [
                    'rest_message' => '"%field" can not be empty.',
                    'soap_message' => sprintf('object has no \'%s\' property', SourceInterface::NAME),
                    'parameters' => [
                        'field' => SourceInterface::NAME,
                    ],
                ],
            ],
            'without_' . SourceInterface::POSTCODE => [
                SourceInterface::POSTCODE,
                [
                    'rest_message' => '"%field" can not be empty.',
                    'soap_message' => sprintf('object has no \'%s\' property', SourceInterface::POSTCODE),
                    'parameters' => [
                        'field' => SourceInterface::POSTCODE,
                    ],
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
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source.php
     */
    public function testFailedValidationOnUpdate($field, $value, array $expectedErrorData)
    {
        $data = $this->validData;
        $data[$field] = $value;

        $source = $this->getSourceDataByName('source-name-1');
        $sourceId = $source[SourceInterface::SOURCE_ID];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sourceId,
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
            'empty_' . SourceInterface::NAME => [
                SourceInterface::NAME,
                null,
                [
                    'message' => '"%field" can not be empty.',
                    'parameters' => [
                        'field' => SourceInterface::NAME,
                    ],
                ],
            ],
            'whitespaces_' . SourceInterface::NAME => [
                SourceInterface::NAME,
                ' ',
                [
                    'message' => '"%field" can not be empty.',
                    'parameters' => [
                        'field' => SourceInterface::NAME,
                    ],
                ],
            ],
            'empty_' . SourceInterface::POSTCODE => [
                SourceInterface::POSTCODE,
                null,
                [
                    'message' => '"%field" can not be empty.',
                    'parameters' => [
                        'field' => SourceInterface::POSTCODE,
                    ],
                ],
            ],
            'whitespaces_' . SourceInterface::POSTCODE => [
                SourceInterface::POSTCODE,
                ' ',
                [
                    'message' => '"%field" can not be empty.',
                    'parameters' => [
                        'field' => SourceInterface::POSTCODE,
                    ],
                ],
            ],
            'empty_' . SourceInterface::COUNTRY_ID => [
                SourceInterface::COUNTRY_ID,
                null,
                [
                    'message' => '"%field" can not be empty.',
                    'parameters' => [
                        'field' => SourceInterface::COUNTRY_ID,
                    ],
                ],
            ],
            'whitespaces_' . SourceInterface::COUNTRY_ID => [
                SourceInterface::COUNTRY_ID,
                ' ',
                [
                    'message' => '"%field" can not be empty.',
                    'parameters' => [
                        'field' => SourceInterface::COUNTRY_ID,
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
            $this->_webApiCall($serviceInfo, ['source' => $data]);
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
    private function getSourceDataByName($name)
    {
        $searchCriteria = [
            'filter_groups' => [
                [
                    'filters' => [
                        [
                            'field' => SourceInterface::NAME,
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
