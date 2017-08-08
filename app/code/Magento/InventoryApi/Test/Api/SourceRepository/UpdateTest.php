<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Test\Api\SourceRepository;

use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\TestFramework\Assert\AssertArrayContains;
use Magento\TestFramework\TestCase\WebapiAbstract;

class UpdateTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/source';
    const RESOURCES_PATH = '/V1/inventory/sources';
    const SERVICE_NAME = 'inventorySourceRepositoryV1';
    /**#@-*/

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source/source_1.php
     */
    public function testUpdate()
    {
        $source = $this->getSourceDataByName('source-name-1');
        $sourceId = $source[SourceInterface::SOURCE_ID];
        $data = [
            SourceInterface::NAME => 'source-name-1-updated',
            SourceInterface::CONTACT_NAME => 'source-contact-name-1-updated',
            SourceInterface::EMAIL => 'source-email-1-updated',
            SourceInterface::ENABLED => false,
            SourceInterface::DESCRIPTION => 'source-description-1-updated',
            SourceInterface::LATITUDE => 0.3,
            SourceInterface::LONGITUDE => 0.2,
            SourceInterface::COUNTRY_ID => 'UK',
            SourceInterface::REGION_ID => 12,
            SourceInterface::CITY => 'source-city-1-updated',
            SourceInterface::STREET => 'source-street-1-updated',
            SourceInterface::POSTCODE => 'source-postcode-1-updated',
            SourceInterface::PHONE => 'source-phone-1-updated',
            SourceInterface::FAX => 'source-fax-1-updated',
            SourceInterface::PRIORITY => 300,
            SourceInterface::USE_DEFAULT_CARRIER_CONFIG => 0,
            SourceInterface::CARRIER_LINKS => [
                [
                    SourceCarrierLinkInterface::CARRIER_CODE => 'ups-updated',
                    SourceCarrierLinkInterface::POSITION => 2000,
                ],
                [
                    SourceCarrierLinkInterface::CARRIER_CODE => 'usps-updated',
                    SourceCarrierLinkInterface::POSITION => 3000,
                ],
            ],
        ];
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
        $this->_webApiCall($serviceInfo, ['source' => $data], null, 'all');

        AssertArrayContains::assert($data, $this->getSourceDataById($sourceId));
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
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCES_PATH . '?' . http_build_query(['searchCriteria' => $searchCriteria]),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, [], null);
        self::assertArrayHasKey('items', $response);
        return reset($response['items']);
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
        $response = $this->_webApiCall($serviceInfo, [], null);
        self::assertArrayHasKey(SourceInterface::SOURCE_ID, $response);
        return $response;
    }
}
