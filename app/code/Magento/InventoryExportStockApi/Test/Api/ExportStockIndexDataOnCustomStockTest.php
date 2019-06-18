<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStockApi\Test\Api;

use Magento\Framework\ObjectManager\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\DataObjectHelper;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;

class ExportStockIndexDataOnCustomStockTest extends WebapiAbstract
{
    const API_PATH = '/V1/inventory/dump-stock-index-data';
    const SERVICE_NAME = 'inventoryExportStockApiExportStockIndexDataV1';

    const EXPORT_PRODUCT_COUNT = 5;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritDoc
     */
    public function setUp()
    {
      $this->objectManager = Bootstrap::getObjectManager();
    }
    /**
     * @return array
     */
    public function executeDataProvider(): array
    {
        return [
            ['website', 'us_website', self::EXPORT_PRODUCT_COUNT]
        ];
    }

    /**
     * Export stock index with Custom stock and custom website.
     *
     * @param string $type
     * @param string $code
     * @param int $expectedResult
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     * @magentoDataFixture Magento/GroupedProduct/_files/product_grouped.php
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @dataProvider executeDataProvider
     * @magentoDbIsolation disabled
     */
    public function testExportStockDataOnCustomStock(string $type, string $code, int $expectedResult): void
    {
        $products = [
            'downloadable-product',
            'virtual-product',
            'configurable_12345',
            'simple_10',
            'grouped-product',
        ];
        $this->assignProductToAdditionalWebsite($products, 'us_website');
        $this->assignWebsiteToStock(20, 'us_website');
        $this->assignProductToSource($products, 'us-1');
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::API_PATH . '/' . $type . '/' . $code,
                'httpMethod' => Request::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute'
            ]
        ];

        $res = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['salesChannelCode' => $code, 'salesChannelType' => $type]);

        self::assertEquals($expectedResult, count($res));
    }


    /**
     * Assign test products to additional website.
     *
     * @param array $sku
     * @param string $websiteCode
     * @return void
     */
    public function assignProductToAdditionalWebsite(array $sku, string $websiteCode): void
    {
        $websiteRepository = Bootstrap::getObjectManager()->get(WebsiteRepositoryInterface::class);
        $websiteId = $websiteRepository->get($websiteCode)->getId();
        $searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter(ProductInterface::SKU, $sku, 'in')->create();
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $products = $productRepository->getList($searchCriteria)->getItems();
        foreach ($products as $product) {
            $product->setWebsiteIds([$websiteId]);
            $productRepository->save($product);
        }
    }

    /**
     * Assign website to stock as sales channel.
     *
     * @param int $stockId
     * @param string $websiteCode
     * @return void
     */
    public function assignWebsiteToStock(int $stockId, string $websiteCode): void
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

    /**
     * Assign products to sources.
     *
     * @param array $skus
     * @param $source
     */
    public function assignProductToSource(array $skus, $source) : void
    {
        $dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
        $sourceItemFactory = Bootstrap::getObjectManager()->get(SourceItemInterfaceFactory::class);
        $sourceItemsSave = Bootstrap::getObjectManager()->get(SourceItemsSaveInterface::class);
        foreach ($skus as $sku) {
        $sourceItemData = [
            SourceItemInterface::SOURCE_CODE => $source,
            SourceItemInterface::SKU => $sku,
            SourceItemInterface::QUANTITY => 30,
            SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
        ];
        $sourceItems = [];
        $sourceItem = $sourceItemFactory->create();
        $dataObjectHelper->populateWithArray($sourceItem, $sourceItemData, SourceItemInterface::class);
        $sourceItems[] = $sourceItem;
        $sourceItemsSave->execute($sourceItems);
        }
    }
}
