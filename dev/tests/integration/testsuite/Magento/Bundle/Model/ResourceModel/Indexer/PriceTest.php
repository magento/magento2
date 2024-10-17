<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\ResourceModel\Indexer;

use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Price;
use Magento\Customer\Model\Group;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Catalog\Model\Product\Price\GetPriceIndexDataByProductId;
use Magento\CatalogInventory\Model\Indexer\Stock;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class PriceTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ActionInterface
     */
    private $indexer;

    /**
     * @var GetPriceIndexDataByProductId
     */
    private $getPriceIndexDataByProductId;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var Stock
     */
    private $stockIndexer;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->indexer = $this->objectManager->get(Price::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->getPriceIndexDataByProductId = $this->objectManager->get(GetPriceIndexDataByProductId::class);
        $this->websiteRepository = $this->objectManager->get(WebsiteRepositoryInterface::class);
        $this->stockIndexer = $this->objectManager->get(Stock::class);
    }

    /**
     * Test get bundle index price if enabled show out off stock
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Bundle/_files/bundle_product_with_dynamic_price.php
     * @magentoConfigFixture default_store cataloginventory/options/show_out_of_stock 1
     *
     * @return void
     */
    public function testExecuteRowWithShowOutOfStock(): void
    {

        $expectedPrices = [
            'price' => 0,
            'final_price' => 0,
            'min_price' => 15.99,
            'max_price' => 15.99,
            'tier_price' => null
        ];
        $product = $this->productRepository->get('simple1');
        $product->setStockData(['qty' => 0]);
        $this->productRepository->save($product);
        $this->stockIndexer->executeRow($product->getId());
        $bundleProduct = $this->productRepository->get('bundle_product_with_dynamic_price');
        $this->indexer->executeRow($bundleProduct->getId());
        $this->assertIndexTableData($bundleProduct->getId(), $expectedPrices);
    }

    #[
        DataFixture(
            BundleProductFixture::class,
            ['sku' => 'bundle1', 'extension_attributes' => ['website_ids' => []]]
        ),
    ]
    public function testExecuteForBundleWithoutWebsites(): void
    {
        $bundleProduct = $this->productRepository->get('bundle1');
        $this->indexer->executeRow($bundleProduct->getId());
    }

    /**
     * Asserts price data in index table.
     *
     * @param int $productId
     * @param array $expectedPrices
     * @return void
     */
    private function assertIndexTableData(int $productId, array $expectedPrices): void
    {
        $data = $this->getPriceIndexDataByProductId->execute(
            $productId,
            Group::NOT_LOGGED_IN_ID,
            (int)$this->websiteRepository->get('base')->getId()
        );
        $data = reset($data);
        foreach ($expectedPrices as $column => $price) {
            $this->assertEquals($price, $data[$column]);
        }
    }
}
