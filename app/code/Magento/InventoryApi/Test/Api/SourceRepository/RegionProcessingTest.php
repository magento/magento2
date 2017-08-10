<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
    const RESOURCE_PATH = '/V1/inventory/source';
    const SERVICE_NAME = 'inventorySourceRepositoryV1';
    /**#@-*/

    public function testCreateWithPredefinedRegion()
    {
        $regionId = 10;
        $data = [
            SourceInterface::NAME => 'source-name',
            SourceInterface::REGION_ID => $regionId,
            SourceInterface::POSTCODE => 'source-postcode',
        ];

        $sourceId = $this->saveSource($data);
        $sourceData = $this->getSourceDataById($sourceId);

        self::assertArrayHasKey(SourceInterface::REGION_ID, $sourceData);
        self::assertEquals($sourceData[SourceInterface::REGION_ID], $regionId);
        self::assertArrayNotHasKey(SourceInterface::REGION, $sourceData);
    }

    public function testCreateWithCustomRegion()
    {
        $regionName = 'custom-region-name';
        $data = [
            SourceInterface::NAME => 'source-name',
            SourceInterface::REGION => $regionName,
            SourceInterface::POSTCODE => 'source-postcode',
        ];

        $sourceId = $this->saveSource($data);
        $sourceData = $this->getSourceDataById($sourceId);

        self::assertArrayHasKey(SourceInterface::REGION, $sourceData);
        self::assertEquals($sourceData[SourceInterface::REGION], $regionName);
        self::assertArrayNotHasKey(SourceInterface::REGION_ID, $sourceData);
    }

    public function testCreateWithBothFilledFields()
    {
        $regionId = 10;
        $regionName = 'custom-region-name';
        $data = [
            SourceInterface::NAME => 'source-name',
            SourceInterface::REGION_ID => $regionId,
            SourceInterface::REGION => $regionName,
            SourceInterface::POSTCODE => 'source-postcode',
        ];

        $sourceId = $this->saveSource($data);
        $sourceData = $this->getSourceDataById($sourceId);

        self::assertArrayHasKey(SourceInterface::REGION_ID, $sourceData);
        self::assertEquals($sourceData[SourceInterface::REGION_ID], $regionId);

        self::assertArrayHasKey(SourceInterface::REGION, $sourceData);
        self::assertEquals($sourceData[SourceInterface::REGION], $regionName);
    }

    protected function tearDown()
    {
        /** @var ResourceConnection $connection */
        $connection = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $connection->getConnection()->delete('inventory_source', [
            SourceInterface::NAME . ' IN (?)' => ['source-name'],
        ]);
        parent::tearDown();
    }

    /**
     * @param array $data
     * @return int
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
        $sourceId = $this->_webApiCall($serviceInfo, ['source' => $data]);
        self::assertTrue(is_numeric($sourceId));
        self::assertNotEmpty($sourceId);
        return $sourceId;
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
        $response = $this->_webApiCall($serviceInfo);
        self::assertArrayHasKey(SourceInterface::SOURCE_ID, $response);
        return $response;
    }
}
