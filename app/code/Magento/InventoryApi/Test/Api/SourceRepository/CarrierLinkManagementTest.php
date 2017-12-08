<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Test\Api\SourceRepository;

use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;

class CarrierLinkManagementTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/source';
    const SERVICE_NAME = 'inventoryApiSourceRepositoryV1';
    /**#@-*/

    /**
     * @param array $carrierLinks
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source.php
     * @dataProvider dataProviderCarrierLinks
     */
    public function testCarrierLinksManagement(array $carrierLinks)
    {
        $sourceId = 10;
        $expectedData = [
            SourceInterface::NAME => 'source-name-1',
            SourceInterface::POSTCODE => 'source-postcode',
            SourceInterface::COUNTRY_ID => 'US',
            SourceInterface::USE_DEFAULT_CARRIER_CONFIG => 0,
            SourceInterface::CARRIER_LINKS => $carrierLinks,
        ];

        $this->saveSource($sourceId, $expectedData);
        $sourceData = $this->getSourceDataById($sourceId);

        self::assertArrayHasKey(SourceInterface::USE_DEFAULT_CARRIER_CONFIG, $sourceData);
        self::assertEquals(
            $expectedData[SourceInterface::USE_DEFAULT_CARRIER_CONFIG],
            $sourceData[SourceInterface::USE_DEFAULT_CARRIER_CONFIG]
        );

        self::assertArrayHasKey(SourceInterface::CARRIER_LINKS, $sourceData);
        self::assertEquals($expectedData[SourceInterface::CARRIER_LINKS], $sourceData[SourceInterface::CARRIER_LINKS]);
    }

    /**
     * @return array
     */
    public function dataProviderCarrierLinks(): array
    {
        return [
            'add_carrier_new_links' => [
                [
                    [
                        SourceCarrierLinkInterface::CARRIER_CODE => 'ups',
                        SourceCarrierLinkInterface::POSITION => 100,
                    ],
                    [
                        SourceCarrierLinkInterface::CARRIER_CODE => 'usps',
                        SourceCarrierLinkInterface::POSITION => 200,
                    ],
                    [
                        SourceCarrierLinkInterface::CARRIER_CODE => 'dhl',
                        SourceCarrierLinkInterface::POSITION => 300,
                    ],
                    [
                        SourceCarrierLinkInterface::CARRIER_CODE => 'fedex',
                        SourceCarrierLinkInterface::POSITION => 400,
                    ],
                ],
            ],
            'replace_carrier_links' => [
                [
                    [
                        SourceCarrierLinkInterface::CARRIER_CODE => 'dhl',
                        SourceCarrierLinkInterface::POSITION => 100,
                    ],
                    [
                        SourceCarrierLinkInterface::CARRIER_CODE => 'fedex',
                        SourceCarrierLinkInterface::POSITION => 200,
                    ],
                ],
            ],
            'delete_carrier_links' => [
                [],
            ],
        ];
    }

    /**
     * @param int $sourceId
     * @param array $data
     * @return void
     */
    private function saveSource(int $sourceId, array $data)
    {
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
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST) {
            $this->_webApiCall($serviceInfo, ['source' => $data]);
        } else {
            $requestData = $data;
            $requestData['sourceId'] = $sourceId;
            $this->_webApiCall($serviceInfo, ['source' => $requestData]);
        }
    }

    /**
     * @param int $sourceId
     * @return array
     */
    private function getSourceDataById(int $sourceId): array
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sourceId,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
        $response = (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['sourceId' => $sourceId]);
        self::assertArrayHasKey(SourceInterface::SOURCE_ID, $response);
        return $response;
    }

    /**
     * @param array $carrierData
     * @param array $expectedErrorData
     * @dataProvider failedValidationDataProvider
     */
    public function testCarrierLinksValidation(array $carrierData, array $expectedErrorData)
    {
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
            $this->_webApiCall($serviceInfo, ['source' => $carrierData]);
            $this->fail('Expected throwing exception');
        } catch (\Exception $e) {
            self::assertEquals($expectedErrorData, $this->processRestExceptionResult($e));
            self::assertEquals(\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST, $e->getCode());
        }
    }

    /**
     * @return array
     */
    public function failedValidationDataProvider(): array
    {
        return [
            'use_global_configuration_chosen' => [
                [
                    SourceInterface::NAME => 'source-name-1',
                    SourceInterface::POSTCODE => 'source-postcode',
                    SourceInterface::COUNTRY_ID => 'US',
                    SourceInterface::USE_DEFAULT_CARRIER_CONFIG => 1,
                    SourceInterface::CARRIER_LINKS => [
                        [
                            SourceCarrierLinkInterface::CARRIER_CODE => 'ups',
                            SourceCarrierLinkInterface::POSITION => 100,
                        ],
                        [
                            SourceCarrierLinkInterface::CARRIER_CODE => 'usps',
                            SourceCarrierLinkInterface::POSITION => 200,
                        ],
                    ],
                ],
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' =>
                                'You can\'t configure "%field" because you have chosen Global Shipping configuration.',
                            'parameters' => [
                                'field' => SourceInterface::CARRIER_LINKS,
                            ],
                        ],
                    ],
                ],
            ],
            'carrier_codes_not_exits' => [
                [
                    SourceInterface::NAME => 'source-name-1',
                    SourceInterface::POSTCODE => 'source-postcode',
                    SourceInterface::COUNTRY_ID => 'US',
                    SourceInterface::USE_DEFAULT_CARRIER_CONFIG => 0,
                    SourceInterface::CARRIER_LINKS => [
                        [
                            SourceCarrierLinkInterface::CARRIER_CODE => 'no_exists_1',
                            SourceCarrierLinkInterface::POSITION => 100,
                        ],
                        [
                            SourceCarrierLinkInterface::CARRIER_CODE => 'no_exists_2',
                            SourceCarrierLinkInterface::POSITION => 200,
                        ],
                    ],
                ],
                [
                    'message' => 'Validation Failed',
                    'errors' => [
                        [
                            'message' => 'Carrier with code: "%carrier" don\'t exists.',
                            'parameters' => [
                                'carrier' => 'no_exists_1'
                            ],
                        ],
                        [
                            'message' => 'Carrier with code: "%carrier" don\'t exists.',
                            'parameters' => [
                                'carrier' => 'no_exists_2'
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
