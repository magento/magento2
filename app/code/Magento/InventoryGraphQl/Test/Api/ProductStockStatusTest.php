<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryGraphQl\Test\Api;

use Magento\Framework\ObjectManagerInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Verify product status on different stocks.
 */
class ProductStockStatusTest extends GraphQlAbstract
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Verify product status on additional stock.
     *
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     */
    public function testProductStockStatusInStockWithSources():void
    {
        $this->assignWebsiteToStock(10, 'base');
        $productSku = 'SKU-1';
        $query = <<<QUERY
        {
            products(filter: {sku: {eq: "{$productSku}"}})
            {
                items {
                    stock_status
                }
            }
        }
QUERY;

        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey(0, $response['products']['items']);
        $this->assertArrayHasKey('stock_status', $response['products']['items'][0]);
        $this->assertEquals('IN_STOCK', $response['products']['items'][0]['stock_status']);
    }

    /**
     * Assign website to stock as sales chanel.
     *
     * @param int $stockId
     * @param string $websiteCode
     * @return void
     */
    private function assignWebsiteToStock(int $stockId, string $websiteCode): void
    {
        $stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
        $salesChannelFactory = Bootstrap::getObjectManager()->get(SalesChannelInterfaceFactory::class);
        $stock = $stockRepository->get($stockId);
        $extensionAttributes = $stock->getExtensionAttributes();
        $salesChannels = $extensionAttributes->getSalesChannels();
        $salesChannel = $salesChannelFactory->create();
        $salesChannel->setCode($websiteCode);
        $salesChannel->setType(SalesChannelInterface::TYPE_WEBSITE);
        $salesChannels[] = $salesChannel;
        $extensionAttributes->setSalesChannels($salesChannels);
        $stockRepository->save($stock);
    }
}
