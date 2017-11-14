<?php

namespace Magento\InventoryConfigurationApi\Test\Api;

use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Rest\Request;

class GetSourceItemConfigurationTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/inventory/configuration';
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
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST) {
            $response = $this->_webApiCall($serviceInfo);
        } else {
            $response =$this->_webApiCall($serviceInfo, ['sourceId' => $sourceId, 'sku' => $sku]);
        }

        $this->assertTrue($response[SourceItemConfigurationInterface::SOURCE_ITEM_ID] == 1 &&
                          $response[SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY] == 2);
    }
}
