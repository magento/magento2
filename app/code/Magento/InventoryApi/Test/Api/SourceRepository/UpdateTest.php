<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Test\Api\SourceRepository;

use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\TestFramework\Assert\AssertArrayContains;
use Magento\TestFramework\TestCase\WebapiAbstract;

class UpdateTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/sources';
    const SERVICE_NAME = 'inventoryApiSourceRepositoryV1';
    /**#@-*/

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source.php
     */
    public function testUpdate()
    {
        $sourceCode = 'source-code-1';
        $expectedData = [
            SourceInterface::NAME => 'source-name-1-updated',
            SourceInterface::CONTACT_NAME => 'source-contact-name-updated',
            SourceInterface::EMAIL => 'source-email-updated',
            SourceInterface::ENABLED => false,
            SourceInterface::DESCRIPTION => 'source-description-updated',
            SourceInterface::LATITUDE => 13.123456,
            SourceInterface::LONGITUDE => 14.123456,
            SourceInterface::COUNTRY_ID => 'UK',
            SourceInterface::REGION_ID => 12,
            SourceInterface::CITY => 'source-city-updated',
            SourceInterface::STREET => 'source-street-updated',
            SourceInterface::POSTCODE => 'source-postcode-updated',
            SourceInterface::PHONE => 'source-phone-updated',
            SourceInterface::FAX => 'source-fax-updated',
            SourceInterface::USE_DEFAULT_CARRIER_CONFIG => 1,
            SourceInterface::CARRIER_LINKS => [],
        ];
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
        if (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST) {
            $this->_webApiCall($serviceInfo, ['source' => $expectedData]);
        } else {
            $requestData = $expectedData;
            $requestData['sourceCode'] = $sourceCode;
            $this->_webApiCall($serviceInfo, ['source' => $requestData]);
        }

        AssertArrayContains::assert($expectedData, $this->getSourceDataByCode($sourceCode));
    }

    /**
     * @param string $sourceCode
     * @return array
     */
    private function getSourceDataByCode(string $sourceCode): array
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sourceCode,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
        $response = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['sourceCode' => $sourceCode]);
        self::assertArrayHasKey(SourceInterface::SOURCE_CODE, $response);
        return $response;
    }
}
