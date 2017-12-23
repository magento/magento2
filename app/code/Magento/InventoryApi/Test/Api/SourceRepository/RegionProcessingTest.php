<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Test\Api\SourceRepository;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Inventory\Model\ResourceModel\Source as SourceResourceModel;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class RegionProcessingTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/source';
    const SERVICE_NAME = 'inventoryApiSourceRepositoryV1';
    /**#@-*/

    public function testCreateWithPredefinedRegion()
    {
        $regionId = 10;
        $data = [
            SourceInterface::NAME => 'source-name-1',
            SourceInterface::POSTCODE => 'source-postcode',
            SourceInterface::COUNTRY_ID => 'US',
            SourceInterface::REGION_ID => $regionId,
        ];

        $sourceId = $this->saveSource($data);
        $sourceData = $this->getSourceDataById($sourceId);

        self::assertArrayHasKey(SourceInterface::REGION_ID, $sourceData);
        self::assertEquals($regionId, $sourceData[SourceInterface::REGION_ID]);
        self::assertArrayNotHasKey(SourceInterface::REGION, $sourceData);
    }

    public function testCreateWithCustomRegion()
    {
        $regionName = 'custom-region-name';
        $data = [
            SourceInterface::NAME => 'source-name-1',
            SourceInterface::POSTCODE => 'source-postcode',
            SourceInterface::COUNTRY_ID => 'US',
            SourceInterface::REGION => $regionName,
        ];

        $sourceId = $this->saveSource($data);
        $sourceData = $this->getSourceDataById($sourceId);

        self::assertArrayHasKey(SourceInterface::REGION, $sourceData);
        self::assertEquals($regionName, $sourceData[SourceInterface::REGION]);
        self::assertArrayNotHasKey(SourceInterface::REGION_ID, $sourceData);
    }

    public function testCreateWithBothFilledFields()
    {
        $regionId = 10;
        $regionName = 'custom-region-name';
        $data = [
            SourceInterface::NAME => 'source-name-1',
            SourceInterface::REGION_ID => $regionId,
            SourceInterface::POSTCODE => 'source-postcode',
            SourceInterface::COUNTRY_ID => 'US',
            SourceInterface::REGION => $regionName,
        ];

        $sourceId = $this->saveSource($data);
        $sourceData = $this->getSourceDataById($sourceId);

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
     * @return int
     */
    private function saveSource(array $data): int
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
        $sourceId = $this->_webApiCall($serviceInfo, ['source' => $data]);
        self::assertTrue(is_numeric($sourceId));
        self::assertNotEmpty($sourceId);
        return $sourceId;
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
        self::assertArrayHasKey(SourceResourceModel::SOURCE_ID_FIELD, $response);
        return $response;
    }
}
