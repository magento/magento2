<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

use Magento\TestFramework\Indexer\TestCase as IndexerTestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Api\CategoryRepositoryInterface;

/**
 * Class RowTest
 */
class RowTest extends IndexerTestCase
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->processor = $this->objectManager->get(Processor::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
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
}
