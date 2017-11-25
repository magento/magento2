<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurationApi\Test\Api;

use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Rest\Request;

class DeleteSourceItemConfigurationTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/inventory/source-item-configuration';
    const SERVICE_NAME_DELETE = 'inventoryConfigurationApiDeleteSourceItemConfigurationV1';
    const SERVICE_NAME_GET = 'inventoryConfigurationApiGetSourceItemConfigurationV1';

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurationApi/Test/_files/source_item_configuration.php
     */
    public function testDeleteSourceItemConfiguration()
    {
        $sourceId = 10;
        $sku = 'SKU-1';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?'
                    . http_build_query(['sourceId' => $sourceId, 'sku' => $sku]),
                'httpMethod' => Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME_DELETE,
                'operation' => self::SERVICE_NAME_DELETE . 'Execute',
            ],
        ];

        (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['sourceId' => $sourceId, 'sku' => $sku]);

        $sourceItemConfiguration = $this->getSourceItemConfiguration($sourceId, $sku);

        self::assertNotEmpty($sourceItemConfiguration);
        $defaultNotifyQtyValue = 1;
        self::assertEquals(
            $defaultNotifyQtyValue,
            $sourceItemConfiguration[SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY]
        );
    }

    /**
     * @param int $sourceId
     * @param string $sku
     * @return array|bool|float|int|string
     */
    private function getSourceItemConfiguration(int $sourceId, string $sku)
    {
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