<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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

/**
 * @magentoDbIsolation disabled
 * @group indexer_dimension
 * @magentoIndexerDimensionMode catalog_product_price website_and_customer_group
 */
class SpecialPriceIndexerWithDimensionTest extends \PHPUnit\Framework\TestCase
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

    /**
     * Use collection to check data in index
     * Do not use magentoDbIsolation because index statement changing "tears" transaction (triggers creating)
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoDataFixture Magento/Catalog/_files/enable_price_index_schedule.php
     */
    public function testFullReindexIfChildHasSpecialPrice()
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

        /** @var Product[] $items */
        $items = array_values($collection->getItems());
        self::assertEquals(10, $items[0]->getData('min_price'));

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
     * @magentoDbIsolation disabled
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
