<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotificationApi\Test\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;

class SourceItemConfigurationsSaveTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/inventory/low-quantity-notification';
    const SERVICE_NAME_GET = 'inventoryLowQuantityNotificationApiGetSourceItemConfigurationV1';
    const SERVICE_NAME_SAVE = 'inventoryLowQuantityNotificationApiSourceItemConfigurationsSaveV1';

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     */
    public function testSaveSourceItemConfiguration()
    {
        $sourceItemConfigurations = [
            [
                SourceItemConfigurationInterface::SOURCE_CODE => 'eu-1',
                SourceItemConfigurationInterface::SKU => 'SKU-1',
                SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY => 2,
            ],
            [
                SourceItemConfigurationInterface::SOURCE_CODE => 'eu-2',
                SourceItemConfigurationInterface::SKU => 'SKU-1',
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

        $this->_webApiCall($serviceInfo, ['sourceItemConfigurations' => $sourceItemConfigurations]);

        $sourceItemConfiguration = $this->getSourceItemConfiguration('eu-1', 'SKU-1');
        self::assertEquals($sourceItemConfigurations[0], $sourceItemConfiguration);

        $sourceItemConfiguration = $this->getSourceItemConfiguration('eu-2', 'SKU-1');
        self::assertEquals($sourceItemConfigurations[1], $sourceItemConfiguration);
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
