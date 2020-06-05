<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Indexer\TestCase;

/**
 * Test for \Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows.
 */
class RowsTest extends TestCase
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->processor = $objectManager->get(Processor::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);
        $this->productCollectionFactory = $objectManager->get(CollectionFactory::class);
        $this->layout = $objectManager->get(LayoutInterface::class);
    }

    /**
     * Test update category products
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     *
     * @return void
     */
    public function testProductsUpdate(): void
    {
        $product = $this->productRepository->getById(1);
        $this->processor->reindexList([$product->getId()]);

        $category = $this->categoryRepository->get(2);
        $listProduct = $this->layout->createBlock(ListProduct::class);
        $layer = $listProduct->getLayer();
        $layer->setCurrentCategory($category);
        $productCollection = $layer->getProductCollection();

        $this->assertCount(1, $productCollection);

        /** @var $productItem Product */
        foreach ($productCollection as $productItem) {
            $this->assertEquals($product->getName(), $productItem->getName());
            $this->assertEquals($product->getShortDescription(), $productItem->getShortDescription());
        }
    }

    /**
     * Products update with different statuses
     *
     * @magentoDbIsolation disabled
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_custom_attribute_in_flat.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     *
     * @return void
     */
    public function testProductsDifferentStatusesUpdate(): void
    {
        $firstProduct = $this->productRepository->get('simple_with_custom_flat_attribute');
        $secondProduct = $this->productRepository->get('simple-1');

        $this->processor->getIndexer()->setScheduled(true);
        $this->productRepository->save($secondProduct->setStatus(Status::STATUS_DISABLED));
        $this->processor->reindexList([$firstProduct->getId(), $secondProduct->getId()], true);
        $collection = $this->productCollectionFactory->create();

        $this->assertCount(1, $collection);
        $this->assertEquals($firstProduct->getId(), $collection->getFirstItem()->getId());
    }
}
