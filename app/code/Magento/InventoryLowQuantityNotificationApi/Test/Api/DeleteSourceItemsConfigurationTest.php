<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotificationApi\Test\Api;

use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Rest\Request;

class DeleteSourceItemsConfigurationTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/inventory/low-quantity-notification';
    const RESOURCE_DELETE_PATH = '/V1/inventory/low-quantity-notifications-delete';
    const SERVICE_NAME_DELETE = 'inventoryLowQuantityNotificationApiDeleteSourceItemsConfigurationV1';
    const SERVICE_NAME_GET = 'inventoryLowQuantityNotificationApiGetSourceItemConfigurationV1';

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryLowQuantityNotificationApi/Test/_files/source_item_configuration.php
     */
    public function testDeleteSourceItemConfiguration()
    {
        $sourceItemsForDelete = [
            [
                SourceItemInterface::SOURCE_CODE => 'eu-1',
                SourceItemInterface::SKU => 'SKU-1',
            ],
            [
                SourceItemInterface::SOURCE_CODE => 'eu-2',
                SourceItemInterface::SKU => 'SKU-3',
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_DELETE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME_DELETE,
                'operation' => self::SERVICE_NAME_DELETE . 'Execute',
            ],
        ];

        $this->_webApiCall($serviceInfo, ['sourceItems' => $sourceItemsForDelete]);

        $sourceItemConfiguration = $this->getSourceItemConfiguration('eu-1', 'SKU-1');
        $defaultNotifyQtyValue = 1;
        self::assertEquals(
            $defaultNotifyQtyValue,
            $sourceItemConfiguration[SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY]
        );

        $sourceItemConfiguration = $this->getSourceItemConfiguration('eu-2', 'SKU-3');
        $defaultNotifyQtyValue = 1;
        self::assertEquals(
            $defaultNotifyQtyValue,
            $sourceItemConfiguration[SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY]
        );
    }

    /**
     * @param string $sourceCode
     * @param string $sku
     * @return array
     */
    private function getSourceItemConfiguration(string $sourceCode, string $sku)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sourceCode . '/' . $sku,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME_GET,
                'operation' => self::SERVICE_NAME_GET . 'Execute',
            ],
        ];
        $sourceItemConfiguration = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['sourceCode' => $sourceCode, 'sku' => $sku]);

        self::assertInternalType('array', $sourceItemConfiguration);
        self::assertNotEmpty($sourceItemConfiguration);

        return $sourceItemConfiguration;
    }
}
