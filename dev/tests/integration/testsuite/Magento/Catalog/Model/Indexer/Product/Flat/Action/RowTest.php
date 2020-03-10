<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Helper\Product\Flat\Indexer as FlatIndexerHelper;
use Magento\TestFramework\Indexer\TestCase as IndexerTestCase;

/**
 * Test for \Magento\Catalog\Model\Indexer\Product\Flat\Action\Row.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RowTest extends IndexerTestCase
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FlatIndexerHelper
     */
    private $flatIndexerHelper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->processor = $this->objectManager->get(Processor::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->flatIndexerHelper = $this->objectManager->get(FlatIndexerHelper::class);
    }

    /**
     * Tests product update
     *
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/row_fixture.php
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     * @magentoAppArea frontend
     *
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function testProductUpdate(): void
    {
        /** @var ListProduct $listProduct */
        $listProduct = $this->objectManager->create(ListProduct::class);

        $this->processor->getIndexer()
            ->setScheduled(false);
        $isScheduled = $this->processor->getIndexer()
            ->isScheduled();
        self::assertFalse(
            $isScheduled,
            'Indexer is in scheduled mode when turned to update on save mode'
        );

        $this->processor->reindexAll();

        $product = $this->productRepository->get('simple');
        $product->setName('Updated Product');
        $this->productRepository->save($product);

        /** @var \Magento\Catalog\Api\Data\CategoryInterface $category */
        $category = $this->categoryRepository->get(9);
        /** @var \Magento\Catalog\Model\Layer $layer */
        $layer = $listProduct->getLayer();
        $layer->setCurrentCategory($category);
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $layer->getProductCollection();
        self::assertTrue(
            $productCollection->isEnabledFlat(),
            'Product collection is not using flat resource when flat is on'
        );

        self::assertEquals(
            2,
            $productCollection->count(),
            'Product collection items count must be exactly 2'
        );

        foreach ($productCollection as $product) {
            /** @var $product \Magento\Catalog\Model\Product */
            if ($product->getSku() === 'simple') {
                self::assertEquals(
                    'Updated Product',
                    $product->getName(),
                    'Product name from flat does not match with updated name'
                );
            }
        }
    }

    /**
     * Assign product to one website
     *
     * @magentoDbIsolation disabled
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_custom_attribute_in_flat.php
     *
     * @return void
     */
    public function testProductToWebsiteAssign(): void
    {
        $secondWebsite = $this->storeManager->getWebsite('test');
        $secondStoreId = current($secondWebsite->getStoreIds());

        $product = $this->productRepository->get('simple-1');
        $this->productRepository->save($product->setWebsiteIds([$secondWebsite->getId()]));

        $secondFlatTable = $this->flatIndexerHelper->getFlatTableName($secondStoreId);

        $resource = $this->objectManager->get(ResourceConnection::class);
        /** @var AdapterInterface $connection */
        $connection = $resource->getConnection();

        $skus = $connection->fetchCol($connection->select()->from($secondFlatTable, ['sku']));

        $this->assertCount(1, $skus);
        $this->assertEquals(current($skus), 'simple-1');
    }
}
