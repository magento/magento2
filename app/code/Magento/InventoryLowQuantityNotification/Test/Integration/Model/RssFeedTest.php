<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Test\Integration\Model;

use Magento\InventoryLowQuantityNotificationAdminUi\Block\Adminhtml\Rss\NotifyStock;
use Magento\InventoryLowQuantityNotificationApi\Api\GetSourceItemConfigurationInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\SourceItemConfigurationsSaveInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test getRssData with different configuration on multi source inventory.
 */
class RssFeedTest extends TestCase
{
    /**
     * @var NotifyStock
     */
    private $dataProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $storeCodeBefore;

    /**
     * @var SourceItemConfigurationsSaveInterface
     */
    private $sourceItemConfigurationsSave;

    /**
     * @var GetSourceItemConfigurationInterface
     */
    private $getSourceItemConfiguration;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->dataProvider = Bootstrap::getObjectManager()->create(NotifyStock::class);
        $this->storeManager = Bootstrap::getObjectManager()->create(StoreManagerInterface::class);
        $this->sourceItemConfigurationsSave = Bootstrap::getObjectManager()
            ->create(SourceItemConfigurationsSaveInterface::class);
        $this->getSourceItemConfiguration = Bootstrap::getObjectManager()
            ->create(GetSourceItemConfigurationInterface::class);
        $this->storeCodeBefore = $this->storeManager->getStore()->getCode();
        $this->storeManager->setCurrentStore(Store::ADMIN_CODE);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @codingStandardsIgnoreLine
     * @magentoDataFixture ../../../../app/code/Magento/InventoryLowQuantityNotificationApi/Test/_files/source_item_configuration.php
     * @magentoConfigFixture default_store cataloginventory/item_options/notify_stock_qty 7
     *
     * @param string $sku
     * @param string $sourceCode
     * @param float $notifyQty
     * @param int $expectedCount
     * @return void
     *
     * @dataProvider getRssDataDataProvider
     */
    public function testGetRssData(
        string $sku,
        string $sourceCode,
        $notifyQty,
        int $expectedCount
    ) {
        $sourceItemConfiguration = $this->getSourceItemConfiguration->execute($sourceCode, $sku);
        $sourceItemConfiguration->setNotifyStockQty($notifyQty);

        $this->sourceItemConfigurationsSave->execute([$sourceItemConfiguration]);

        $data = $this->dataProvider->getRssData();

        $this->assertEquals($expectedCount, count($data['entries']));
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @codingStandardsIgnoreLine
     * @magentoDataFixture ../../../../app/code/Magento/InventoryLowQuantityNotificationApi/Test/_files/source_item_configuration.php
     * @codingStandardsIgnoreLine
     * @magentoDataFixture ../../../../app/code/Magento/InventoryLowQuantityNotificationApi/Test/_files/enable_manage_stock_for_products.php
     * @magentoConfigFixture default_store cataloginventory/item_options/notify_stock_qty 7
     * @magentoConfigFixture default_store cataloginventory/item_options/manage_stock 0
     *
     * @param string $sku
     * @param string $sourceCode
     * @param float $notifyQty
     * @param int $expectedCount
     * @return void
     *
     * @dataProvider getRssDataDataProvider
     */
    public function testGetRssDataDisabledManageStock(
        string $sku,
        string $sourceCode,
        $notifyQty,
        int $expectedCount
    ) {
        $sourceItemConfiguration = $this->getSourceItemConfiguration->execute($sourceCode, $sku);
        $sourceItemConfiguration->setNotifyStockQty($notifyQty);

        $this->sourceItemConfigurationsSave->execute([$sourceItemConfiguration]);

        $data = $this->dataProvider->getRssData();

        $this->assertEquals($expectedCount, count($data['entries']));
    }

    /**
     * @return array
     */
    public function getRssDataDataProvider(): array
    {
        return [
            ['SKU-1', 'eu-disabled', 12, 3],
            ['SKU-1', 'eu-disabled', 6, 2],
            ['SKU-1', 'eu-disabled', null, 2],
            ['SKU-1', 'eu-1', 6, 3],
            ['SKU-1', 'eu-1', 5.4, 2],
            ['SKU-1', 'eu-1', null, 3],
            ['SKU-2', 'us-1', 8, 4],
            ['SKU-2', 'us-1', 1, 3],
            ['SKU-2', 'us-1', null, 4],
            ['SKU-3', 'eu-2', 10, 3],
            ['SKU-3', 'eu-2', 5, 2],
            ['SKU-3', 'eu-2', null, 3],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        parent::tearDown();

        if (null !== $this->storeCodeBefore) {
            $this->storeManager->setCurrentStore($this->storeCodeBefore);
        }
    }
}
