<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Collection test
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->collection = Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class
        );

        $this->processor = Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Indexer\Product\Price\Processor::class
        );

        $this->productRepository = Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     */
    public function testAddPriceDataOnSchedule()
    {
        $this->processor->getIndexer()->setScheduled(true);
        $this->assertTrue($this->processor->getIndexer()->isScheduled());

        $productRepository = Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
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

        $this->collection = Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class
        );
        $this->collection->addPriceData(0, 1);
        $this->collection->load();

        /** @var \Magento\Catalog\Api\Data\ProductInterface[] $product */
        $items = $this->collection->getItems();
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = reset($items);
        $this->assertCount(2, $items);
        $this->assertEquals(15, $product->getPrice());

        $this->processor->getIndexer()->setScheduled(false);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     */
    public function testSetVisibility()
    {
        $appState = Bootstrap::getObjectManager()
            ->create(State::class);
        $appState->setAreaCode(Area::AREA_CRONTAB);
        $this->collection->setStoreId(Store::DEFAULT_STORE_ID);
        $this->collection->setVisibility([Visibility::VISIBILITY_BOTH]);
        $this->collection->load();
        /** @var \Magento\Catalog\Api\Data\ProductInterface[] $product */
        $items = $this->collection->getItems();
        $this->assertCount(2, $items);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_product.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     */
    public function testSetCategoryWithStoreFilter()
    {
        $appState = Bootstrap::getObjectManager()
            ->create(State::class);
        $appState->setAreaCode(Area::AREA_CRONTAB);

        $category = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Catalog\Model\Category::class
        )->load(333);
        $this->collection->addCategoryFilter($category)->addStoreFilter(1);
        $this->collection->load();

        $collectionStoreFilterAfter = Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class
        )->create();
        $collectionStoreFilterAfter->addStoreFilter(1)->addCategoryFilter($category);
        $collectionStoreFilterAfter->load();
        $this->assertEquals($this->collection->getItems(), $collectionStoreFilterAfter->getItems());
        $this->assertCount(1, $collectionStoreFilterAfter->getItems());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     */
    public function testSetCategoryFilter()
    {
        $appState = Bootstrap::getObjectManager()
            ->create(State::class);
        $appState->setAreaCode(Area::AREA_CRONTAB);

        $category = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Catalog\Model\Category::class
        )->load(3);
        $this->collection->addCategoryFilter($category);
        $this->collection->load();
        $this->assertEquals($this->collection->getSize(), 3);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     */
    public function testAddPriceDataOnSave()
    {
        $this->processor->getIndexer()->setScheduled(false);
        $this->assertFalse($this->processor->getIndexer()->isScheduled());
        $productRepository = Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
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
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/product_simple.php
     * @magentoDbIsolation enabled
     */
    public function testGetProductsWithTierPrice()
    {
        $product = $this->productRepository->get('simple products');
        $items = $this->collection->addIdFilter($product->getId())->addAttributeToSelect('price')
            ->load()->addTierPriceData();
        $tierPrices = $items->getFirstItem()->getTierPrices();
        $this->assertCount(3, $tierPrices);
        $this->assertEquals(50, $tierPrices[2]->getExtensionAttributes()->getPercentageValue());
        $this->assertEquals(5, $tierPrices[2]->getValue());
    }

    /**
     * Test addAttributeToSort() with attribute 'is_saleable' works properly on frontend.
     *
     * @dataProvider addAttributeToSortDataProvider
     * @magentoDataFixture Magento/Catalog/_files/multiple_products_with_non_saleable_product.php
     * @magentoConfigFixture current_store cataloginventory/options/show_out_of_stock 1
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testAddAttributeToSort(string $productSku, string $order)
    {
        /** @var Collection $productCollection */
        $this->collection->addAttributeToSort('is_saleable', $order);
        self::assertEquals(2, $this->collection->count());
        self::assertSame($productSku, $this->collection->getFirstItem()->getSku());
    }

    /**
     * Provide test data for testAddAttributeToSort().
     *
     * @return array
     */
    public function addAttributeToSortDataProvider()
    {
        return [
            [
                'product_sku' => 'simple_saleable',
                'order' => Collection::SORT_ORDER_DESC,
            ],
            [
                'product_sku' => 'simple_not_saleable',
                'order' => Collection::SORT_ORDER_ASC,
            ]
        ];
    }

    /**
     * Checks a case if table for join specified as an array.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testJoinTable()
    {
        $this->collection->joinTable(
            ['alias' => 'url_rewrite'],
            'entity_id = entity_id',
            ['request_path'],
            '{{table}}.entity_type = \'product\'',
            'left'
        );
        $sql = (string) $this->collection->getSelect();
        $productTable = $this->collection->getTable('catalog_product_entity');
        $urlRewriteTable = $this->collection->getTable('url_rewrite');

        // phpcs:ignore Magento2.SQL.RawQuery
        $expected = 'SELECT `e`.*, `alias`.`request_path` FROM `' . $productTable . '` AS `e`'
            . ' LEFT JOIN `' . $urlRewriteTable . '` AS `alias` ON (alias.entity_id =e.entity_id)'
            . ' AND (alias.entity_type = \'product\')';

        self::assertStringContainsString($expected,str_replace(PHP_EOL, '', $sql));
    }

    /**
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/few_simple_products.php
     * @magentoDbIsolation enabled
     */
    public function testAddAttributeToFilterAffectsGetSize(): void
    {
        $this->assertEquals(10, $this->collection->getSize());
        $this->collection->addAttributeToFilter('sku', 'Product1');
        $this->assertEquals(1, $this->collection->getSize());
    }

    /**
     * Add tier price attribute filter to collection with different condition types.
     *
     * @param mixed $condition
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/few_simple_products.php
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/product_simple.php
     *
     * @dataProvider addAttributeTierPriceToFilterDataProvider
     */
    public function testAddAttributeTierPriceToFilter($condition): void
    {
        $this->collection->addAttributeToFilter('tier_price', $condition);
        $this->assertEquals(1, $this->collection->getSize());
    }

    /**
     * @return array
     */
    public function addAttributeTierPriceToFilterDataProvider(): array
    {
        return [
            'condition is array' => [['eq' => 8]],
            'condition is string' => ['8'],
            'condition is int' => [8],
            'condition is null' => [null]
        ];
    }

    /**
     * Add is_saleable attribute filter to collection with different condition types.
     *
     * @param mixed $condition
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/product_simple.php
     *
     * @dataProvider addAttributeIsSaleableToFilterDataProvider
     */
    public function testAddAttributeIsSaleableToFilter($condition): void
    {
        $this->collection->addAttributeToFilter('is_saleable', $condition);
        $this->assertEquals(1, $this->collection->getSize());
    }

    /**
     * @return array
     */
    public function addAttributeIsSaleableToFilterDataProvider(): array
    {
        return [
            'condition is array' => [['eq' => 1]],
            'condition is string' => ['1'],
            'condition is int' => [1],
            'condition is null' => [null]
        ];
    }
}
