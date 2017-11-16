<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryConfigurationApi\Test\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterfaceFactory;
use Magento\TestFramework\TestCase\WebapiAbstract;

class SourceItemConfigurationsSaveTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/inventory/configuration';
    const SERVICE_NAME_GET = 'inventoryConfigurationApiGetSourceItemConfigurationV1';
    const SERVICE_NAME_SAVE = 'inventoryConfigurationApiSourceItemConfigurationsSaveV1';

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     */
    public function testSaveSourceItemConfiguration()
    {
        $sourceItemsConfiguration = [
            [
                SourceItemConfigurationInterface::SOURCE_ITEM_ID => 1,
                SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY => 2,
            ],
            [
                SourceItemConfigurationInterface::SOURCE_ITEM_ID => 2,
                SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY => 1,
            ]
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME_SAVE,
                'operation' => self::SERVICE_NAME_SAVE . 'Execute',
            ],
        ];

        $this->_webApiCall($serviceInfo, ['configuration' => $sourceItemsConfiguration]);
        $itemConfiguration = $this->getSourceItemConfiguration();

        $this->assertEquals($sourceItemsConfiguration[0], $itemConfiguration);
    }

    protected function getSourceItemConfiguration()
    {
        $sourceId = 10;
        $sku = 'SKU-1';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sourceId . '/' . $sku,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME_GET,
                'operation' => self::SERVICE_NAME_GET . 'get',
            ],
        ];

        return (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['sourceId' => $sourceId, 'sku' => $sku]);
    }
}
