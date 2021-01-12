<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Check stock alert grid
 *
 * @see \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts\Stock
 *
 * @magentoAppArea adminhtml
 */
class StockTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Stock */
    private $block;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Stock::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * @dataProvider alertsDataProvider
     *
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/ProductAlert/_files/product_alert.php
     * @magentoDataFixture Magento/ProductAlert/_files/stock_alert_on_second_website.php
     *
     * @param string $sku
     * @param string $expectedEmail
     * @param string|null $storeCode
     * @return void
     */
    public function testGridCollectionWithStoreId(string $sku, string $expectedEmail, ?string $storeCode = null): void
    {
        $productId = (int)$this->productRepository->get($sku)->getId();
        $storeId = $storeCode ? (int)$this->storeManager->getStore($storeCode)->getId() : null;
        $this->block->getRequest()->setParams(['id' => $productId, 'store' => $storeId]);
        $collection = $this->block->getPreparedCollection();
        $this->assertCount(1, $collection);
        $this->assertEquals($expectedEmail, $collection->getFirstItem()->getEmail());
    }

    /**
     * @return array
     */
    public function alertsDataProvider(): array
    {
        return [
            'without_store_id_filter' => [
                'product_sku' => 'simple',
                'expected_customer_emails' => 'customer@example.com',
            ],
            'with_store_id_filter' => [
                'product_sku' => 'simple_on_second_website',
                'expected_customer_emails' => 'customer_second_ws_with_addr@example.com',
                'store_code' => 'fixture_third_store',
            ],
        ];
    }
}
