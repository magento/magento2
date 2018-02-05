<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $processor;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\ResourceModel\Product\Collection'
        );

        $this->processor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Indexer\Product\Price\Processor'
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoAppIsolation enabled
     */
    public function testAddPriceDataOnSchedule()
    {
        $this->processor->getIndexer()->setScheduled(true);
        $this->assertTrue($this->processor->getIndexer()->isScheduled());
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Api\ProductRepositoryInterface');
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $productRepository->get('simple');
        $this->assertEquals(10, $product->getPrice());
        $product->setPrice(15);
        $productRepository->save($product);
        $this->collection->addPriceData(0, 1);
        $this->collection->load();
        /** @var \Magento\Catalog\Api\Data\ProductInterface[] $product */
        $items = $this->collection->getItems();
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = reset($items);
        $this->assertCount(2, $items);
        $this->assertEquals(10, $product->getPrice());

        //reindexing
        $this->processor->getIndexer()->reindexList([1]);

        $this->collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\ResourceModel\Product\Collection'
        );
        $this->collection->addPriceData(0, 1);
        $this->collection->load();

        /** @var \Magento\Catalog\Api\Data\ProductInterface[] $product */
        $items = $this->collection->getItems();
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = reset($items);
        $this->assertCount(2, $items);
        $this->assertEquals(15, $product->getPrice());
        $this->processor->getIndexer()->reindexList([1]);

        $this->processor->getIndexer()->setScheduled(false);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoAppIsolation enabled
     */
    public function testAddPriceDataOnSave()
    {
        $this->processor->getIndexer()->setScheduled(false);
        $this->assertFalse($this->processor->getIndexer()->isScheduled());
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Api\ProductRepositoryInterface');
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $productRepository->get('simple');
        $this->assertNotEquals(15, $product->getPrice());
        $product->setPrice(15);
        $productRepository->save($product);
        $this->collection->addPriceData(0, 1);
        $this->collection->load();
        /** @var \Magento\Catalog\Api\Data\ProductInterface[] $product */
        $items = $this->collection->getItems();
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = reset($items);
        $this->assertCount(2, $items);
        $this->assertEquals(15, $product->getPrice());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testAddTierPrice()
    {
        $this->assertEquals($this->collection->getFlag('tier_price_added'), false);

        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Api\ProductRepositoryInterface');

        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $productRepository->get('simple');
        $this->assertEquals(3, count($product->getTierPrices()));

        $product->setTierPrices([]);
        $this->assertEquals(0, count($product->getTierPrices()));

        $this->collection->addTierPriceData();
        $this->collection->load();

        $items = $this->collection->getItems();
        $product = reset($items);
        $this->assertEquals(3, count($product->getTierPrices()));
    }
}
