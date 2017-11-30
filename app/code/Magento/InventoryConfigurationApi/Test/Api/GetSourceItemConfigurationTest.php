<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurationApi\Test\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;

class GetSourceItemConfigurationTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/inventory/source-item-configuration';
    const SERVICE_NAME = 'inventoryConfigurationApiGetSourceItemConfigurationV1';

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryConfigurationApi/Test/_files/source_item_configuration.php
     */
    public function testGetSourceItemConfiguration()
    {
        $sourceId = 10;
        $sku = 'SKU-1';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sourceId . '/' . $sku,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
        ];

        $sourceItemConfiguration = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['sourceId' => $sourceId, 'sku' => $sku]);

        self::assertInternalType('array', $sourceItemConfiguration);
        self::assertNotEmpty($sourceItemConfiguration);

        self::assertEquals($sourceId, $sourceItemConfiguration[SourceItemConfigurationInterface::SOURCE_ID]);
        self::assertEquals($sku, $sourceItemConfiguration[SourceItemConfigurationInterface::SKU]);
        self::assertEquals(2, $sourceItemConfiguration[SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY]);
    }
}
