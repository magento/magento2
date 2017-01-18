<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;

class SpecialPriceIndexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var Processor
     */
    private $indexerProcessor;

    protected function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->productCollectionFactory = Bootstrap::getObjectManager()->get(CollectionFactory::class);
        $this->indexerProcessor = Bootstrap::getObjectManager()->get(Processor::class);
    }

    protected function tearDown()
    {
        $this->indexerProcessor->getIndexer()->setScheduled(false);
    }

    /**
     * Use collection to check data in index
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoDbIsolation enabled
     */
    public function testFullReindexIfChildHasSpecialPrice()
    {
        // Disable update on save
        $this->indexerProcessor->getIndexer()->setScheduled(true);

        $specialPrice = 2;
        /** @var Product $childProduct */
        $childProduct = $this->productRepository->get('simple_10', true);
        $childProduct->setData('special_price', $specialPrice);
        $this->productRepository->save($childProduct);

        /** @var ProductCollection $collection */
        $collection = $this->productCollectionFactory->create();
        $collection
            ->addPriceData()
            ->addFieldToFilter(ProductInterface::SKU, 'configurable');

        /** @var Product[] $items */
        $items = array_values($collection->getItems());
        self::assertEquals(10, $items[0]->getData('min_price'));

        // Reindex
        $this->indexerProcessor->reindexAll();

        /** @var ProductCollection $collection */
        $collection = $this->productCollectionFactory->create();
        $collection
            ->addPriceData()
            ->addFieldToFilter(ProductInterface::SKU, 'configurable');

        /** @var Product $item */
        $item = $collection->getFirstItem();
        self::assertEquals($specialPrice, $item->getData('min_price'));
    }

    /**
     * Use collection to check data in index
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testOnSaveIndexationIfChildHasSpecialPrice()
    {
        $specialPrice = 2;
        /** @var Product $childProduct */
        $childProduct = $this->productRepository->get('simple_10', true);
        $childProduct->setData('special_price', $specialPrice);
        $this->productRepository->save($childProduct);

        /** @var ProductCollection $collection */
        $collection = $this->productCollectionFactory->create();
        $collection
            ->addPriceData()
            ->addFieldToFilter(ProductInterface::SKU, 'configurable');

        /** @var Product $item */
        $item = $collection->getFirstItem();
        self::assertEquals($specialPrice, $item->getData('min_price'));
    }
}
