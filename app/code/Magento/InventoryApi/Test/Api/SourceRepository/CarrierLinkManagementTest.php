<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source/source.php
     * @dataProvider dataProviderCarrierLinks
     */
    public function testCarrierLinksManagement(array $carrierLinks)
    {
        $source = $this->getSourceDataByName('source-name-1');
        $sourceId = $source[SourceInterface::SOURCE_ID];
        $expectedData = [
            SourceInterface::NAME => 'source-name-1',
            SourceInterface::POSTCODE => 'source-postcode',
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
    public function dataProviderCarrierLinks()
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
                        SourceCarrierLinkInterface::CARRIER_CODE => 'new-link-1',
                        SourceCarrierLinkInterface::POSITION => 300,
                    ],
                    [
                        SourceCarrierLinkInterface::CARRIER_CODE => 'new-link-2',
                        SourceCarrierLinkInterface::POSITION => 400,
                    ],
                ],
            ],
            'replace_carrier_links' => [
                [
                    [
                        SourceCarrierLinkInterface::CARRIER_CODE => 'new-link-1',
                        SourceCarrierLinkInterface::POSITION => 100,
                    ],
                    [
                        SourceCarrierLinkInterface::CARRIER_CODE => 'new-link-2',
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
     * TODO: add validator
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source/source.php
     */
    public function testAssignCarrierLinksIfUseGlobalConfigurationChosen()
    {
        $source = $this->getSourceDataByName('source-name-1');
        $sourceId = $source[SourceInterface::SOURCE_ID];
        $expectedData = [
            SourceInterface::NAME => 'source-name-1',
            SourceInterface::POSTCODE => 'source-postcode',
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

    /**
     * @param int $sourceId
     * @param array $data
     * @return void
     */
    private function saveSource($sourceId, array $data)
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
    private function getSourceDataById($sourceId)
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
}
