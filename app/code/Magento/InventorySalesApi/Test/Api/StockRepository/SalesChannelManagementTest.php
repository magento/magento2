<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Test\Api\StockRepository;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;

class SalesChannelManagementTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/stock';
    const SERVICE_NAME = 'inventoryApiStockRepositoryV1';
    /**#@-*/

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     */
    public function testCreateStockWithSalesChannels()
    {
        $stockId = 10;
        $salesChannels = [
            [
                SalesChannelInterface::TYPE => SalesChannelInterface::TYPE_WEBSITE,
                SalesChannelInterface::CODE => 'eu_website',
            ],
            [
                SalesChannelInterface::TYPE => SalesChannelInterface::TYPE_WEBSITE,
                SalesChannelInterface::CODE => 'us_website',
            ],
        ];
        $stockData = [
            StockInterface::NAME => 'stock-name',
            ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY => [
                'sales_channels' => $salesChannels,
            ],
        ];
        $this->saveStock($stockId, $stockData);
        $actualStockData = $this->getStockDataById($stockId);

        self::assertArrayHasKey('sales_channels', $stockData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);
        self::assertEquals(
            $salesChannels,
            $actualStockData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['sales_channels']
        );
    }

    /**
     * @param array $salesChannels
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_with_sales_channels.php
     * @dataProvider updateStockWithSalesChannelsReplacingDataProvider
     */
    public function testUpdateStockWithSalesChannelsReplacing(array $salesChannels)
    {
        $stockId = 10;
        $stockData = [
            StockInterface::NAME => 'stock-name',
            ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY => [
                'sales_channels' => $salesChannels,
            ],
        ];
        $this->saveStock($stockId, $stockData);
        $actualStockData = $this->getStockDataById($stockId);

        self::assertArrayHasKey('sales_channels', $stockData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);
        self::assertEquals(
            $salesChannels,
            $actualStockData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['sales_channels']
        );
    }

    /**
     * @return array
     */
    public function updateStockWithSalesChannelsReplacingDataProvider(): array
    {
        return [
            'replace_sales_channels' => [
                [
                    [
                        SalesChannelInterface::TYPE => SalesChannelInterface::TYPE_WEBSITE,
                        SalesChannelInterface::CODE => 'global_website',
                    ],
                    [
                        SalesChannelInterface::TYPE => SalesChannelInterface::TYPE_WEBSITE,
                        SalesChannelInterface::CODE => 'us_website',
                    ],
                ],
            ],
            'remove_sales_channels' => [[]],
        ];
    }

    /**
     * @param int $stockId
     * @param array $data
     * @return void
     */
    private function saveStock(int $stockId, array $data)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $stockId,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST) {
            $this->_webApiCall($serviceInfo, ['stock' => $data]);
        } else {
            $requestData = $data;
            $requestData['stockId'] = $stockId;
            $this->_webApiCall($serviceInfo, ['stock' => $requestData]);
        }
    }

    /**
     * @param int $stockId
     * @return array
     */
    private function getStockDataById(int $stockId): array
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $stockId,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
        $response = (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['stockId' => $stockId]);
        self::assertArrayHasKey(StockInterface::STOCK_ID, $response);
        return $response;
    }
}
