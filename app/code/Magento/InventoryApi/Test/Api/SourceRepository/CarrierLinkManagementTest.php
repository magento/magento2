<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Test\Api\SourceRepository;

use Magento\Framework\Webapi\Exception;
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
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source.php
     */
    public function testAssignCarrierLinksIfUseGlobalConfigurationChosen()
    {
        $sourceId = 10;
        $expectedData = [
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
        ];

        $expectedErrorData = [
            'message' => 'Validation Failed',
            'errors' => [
                [
                    'message' => 'You can\'t configure "%field" because you have chosen Global Shipping configuration.',
                    'parameters' => [
                        'field' => SourceInterface::CARRIER_LINKS,
                    ],
                ],
            ],
        ];

        try {
            $this->saveSource($sourceId, $expectedData);
            $this->fail('Expected throwing exception');
        } catch (\Exception $e) {
            if (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST) {
                self::assertEquals($expectedErrorData, $this->processRestExceptionResult($e));
                self::assertEquals(Exception::HTTP_BAD_REQUEST, $e->getCode());
            } elseif (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
                $this->assertInstanceOf('SoapFault', $e);
                // @see \Magento\TestFramework\TestCase\WebapiAbstract::getActualWrappedErrors()
                $expectedWrappedErrors = $expectedErrorData['errors'];
                $expectedWrappedErrors[0]['params'] = $expectedWrappedErrors[0]['parameters'];
                unset($expectedWrappedErrors[0]['parameters']);

                $this->checkSoapFault($e, $expectedErrorData['message'], 'env:Sender', [], $expectedWrappedErrors);
            } else {
                throw $e;
            }
        }
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
}
