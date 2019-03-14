<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Test\Api\SourceRepository;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class RegionProcessingTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/sources';
    const SERVICE_NAME = 'inventoryApiSourceRepositoryV1';
    /**#@-*/

    public function testCreateWithPredefinedRegion()
    {
        $sourceCode = 'source-code-1';
        $regionId = 10;
        $data = [
            SourceInterface::SOURCE_CODE => $sourceCode,
            SourceInterface::NAME => 'source-name-1',
            SourceInterface::POSTCODE => 'source-postcode',
            SourceInterface::COUNTRY_ID => 'US',
            SourceInterface::REGION_ID => $regionId,
        ];

        $this->saveSource($data);
        $sourceData = $this->getSourceDataByCode($sourceCode);

        self::assertArrayHasKey(SourceInterface::REGION_ID, $sourceData);
        self::assertEquals($regionId, $sourceData[SourceInterface::REGION_ID]);
        self::assertArrayNotHasKey(SourceInterface::REGION, $sourceData);
    }

    public function testCreateWithCustomRegion()
    {
        $sourceCode = 'source-code-1';
        $regionName = 'custom-region-name';
        $data = [
            SourceInterface::SOURCE_CODE => $sourceCode,
            SourceInterface::NAME => 'source-name-1',
            SourceInterface::POSTCODE => 'source-postcode',
            SourceInterface::COUNTRY_ID => 'US',
            SourceInterface::REGION => $regionName,
        ];

        $this->saveSource($data);
        $sourceData = $this->getSourceDataByCode($sourceCode);

        self::assertArrayHasKey(SourceInterface::REGION, $sourceData);
        self::assertEquals($regionName, $sourceData[SourceInterface::REGION]);
        self::assertArrayNotHasKey(SourceInterface::REGION_ID, $sourceData);
    }

    public function testCreateWithBothFilledFields()
    {
        $sourceCode = 'source-code-1';
        $regionId = 10;
        $regionName = 'custom-region-name';
        $data = [
            SourceInterface::SOURCE_CODE => $sourceCode,
            SourceInterface::NAME => 'source-name-1',
            SourceInterface::REGION_ID => $regionId,
            SourceInterface::POSTCODE => 'source-postcode',
            SourceInterface::COUNTRY_ID => 'US',
            SourceInterface::REGION => $regionName,
        ];

        $this->saveSource($data);
        $sourceData = $this->getSourceDataByCode($sourceCode);

        self::assertArrayHasKey(SourceInterface::REGION_ID, $sourceData);
        self::assertEquals($regionId, $sourceData[SourceInterface::REGION_ID]);

        self::assertArrayHasKey(SourceInterface::REGION, $sourceData);
        self::assertEquals($regionName, $sourceData[SourceInterface::REGION]);
    }

    protected function tearDown()
    {
        /** @var ResourceConnection $connection */
        $connection = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $connection->getConnection()->delete($connection->getTableName('inventory_source'), [
            SourceInterface::NAME . ' IN (?)' => ['source-name-1'],
        ]);
        parent::tearDown();
    }

    /**
     * @param array $data
     * @return void
     */
    private function saveSource(array $data)
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
        $this->_webApiCall($serviceInfo, ['source' => $data]);
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
