<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ProductRepository;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\PriceScope as PriceScopeFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Model\Group;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Products returned by ProductRepository::getList() carry the same data as products loaded one by one
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ListItemsPreloaderTest extends TestCase
{
    private const COMPARED_FIELDS = ['media_gallery_entries', 'tier_prices', 'options', 'extension_attributes'];

    private const PRELOADED_TABLES = [
        'catalog_product_website',
        'catalog_product_entity_tier_price',
        'catalog_product_entity_media_gallery',
    ];

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    #[
        DbIsolation(false),
        AppIsolation(true),
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website2.id$'], 'group2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$group2.id$'], 'store2'),
        DataFixture(
            ProductFixture::class,
            [
                'media_gallery_entries' => [[], ['disabled' => true], []],
                'tier_prices' => [
                    ['customer_group_id' => Group::NOT_LOGGED_IN_ID, 'qty' => 5, 'value' => 8],
                    ['customer_group_id' => Group::CUST_GROUP_ALL, 'qty' => 2, 'value' => 9],
                ],
                'options' => [['type' => ProductCustomOptionInterface::OPTION_TYPE_FIELD]],
                'website_ids' => [1, '$website2.id$'],
            ],
            'product1'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'options' => [
                    [
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                        'values' => [['title' => 'Value 1'], ['title' => 'Value 2']],
                    ],
                ],
            ],
            'product2'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'media_gallery_entries' => [[]],
                'tier_prices' => [['customer_group_id' => 1, 'qty' => 3, 'value' => 7]],
            ],
            'product3'
        ),
    ]
    public function testGetListMatchesSingleProductLoad(): void
    {
        $listed = $this->assertListMatchesSingleProductLoad(['product1', 'product2', 'product3']);

        $product1 = $listed[$this->fixtures->get('product1')->getSku()];
        $this->assertCount(3, $product1['media_gallery_entries']);
        $this->assertCount(2, $product1['tier_prices']);
        $this->assertCount(2, $product1['extension_attributes']['website_ids']);
        $this->assertCount(2, $listed[$this->fixtures->get('product2')->getSku()]['options'][0]['values']);
    }

    #[
        DataFixture(
            ProductFixture::class,
            [
                'media_gallery_entries' => [[], []],
                'tier_prices' => [['customer_group_id' => Group::CUST_GROUP_ALL, 'qty' => 2, 'value' => 9]],
            ],
            'product1'
        ),
        DataFixture(ProductFixture::class, ['media_gallery_entries' => [[]]], 'product2'),
        DataFixture(
            ProductFixture::class,
            ['tier_prices' => [['customer_group_id' => 1, 'qty' => 3, 'value' => 7]]],
            'product3'
        ),
    ]
    public function testGetListQueryCountDoesNotGrowWithPageSize(): void
    {
        $this->assertSame(
            $this->countListSelects(['product1']),
            $this->countListSelects(['product1', 'product2', 'product3'])
        );
    }

    #[
        DataFixture(PriceScopeFixture::class, ['scope' => ProductAttributeInterface::SCOPE_WEBSITE_TEXT]),
        DataFixture(
            ProductFixture::class,
            [
                'tier_prices' => [
                    ['customer_group_id' => Group::NOT_LOGGED_IN_ID, 'qty' => 5, 'value' => 8],
                    ['customer_group_id' => Group::CUST_GROUP_ALL, 'qty' => 2, 'value' => 9],
                ],
            ],
            'product1'
        ),
        DataFixture(ProductFixture::class, as: 'product2'),
        DataFixture(
            ProductFixture::class,
            ['tier_prices' => [['customer_group_id' => 1, 'qty' => 3, 'value' => 7]]],
            'product3'
        ),
    ]
    public function testGetListMatchesSingleProductLoadWithWebsitePriceScope(): void
    {
        $listed = $this->assertListMatchesSingleProductLoad(['product1', 'product2', 'product3']);

        $this->assertCount(2, $listed[$this->fixtures->get('product1')->getSku()]['tier_prices']);
    }

    /**
     * Compare data of products from getList() with the same products loaded one by one
     *
     * @param string[] $fixtureNames
     * @return array
     */
    private function assertListMatchesSingleProductLoad(array $fixtureNames): array
    {
        $skus = [];
        foreach ($fixtureNames as $fixtureName) {
            $skus[] = $this->fixtures->get($fixtureName)->getSku();
        }
        $searchCriteria = Bootstrap::getObjectManager()->create(SearchCriteriaBuilder::class)
            ->addFilter(ProductInterface::SKU, $skus, 'in')
            ->create();

        $this->productRepository->cleanCache();
        $listed = [];
        foreach ($this->productRepository->getList($searchCriteria)->getItems() as $product) {
            $listed[$product->getSku()] = $this->toComparableArray($product);
        }

        $this->productRepository->cleanCache();
        $loaded = [];
        foreach ($skus as $sku) {
            $loaded[$sku] = $this->toComparableArray($this->productRepository->get($sku, false, null, true));
        }

        ksort($listed);
        ksort($loaded);
        $this->assertSame($loaded, $listed);

        return $listed;
    }

    /**
     * Count SELECT statements per preloaded table while listing and serializing the products
     *
     * @param string[] $fixtureNames
     * @return array
     */
    private function countListSelects(array $fixtureNames): array
    {
        $skus = [];
        foreach ($fixtureNames as $fixtureName) {
            $skus[] = $this->fixtures->get($fixtureName)->getSku();
        }
        $objectManager = Bootstrap::getObjectManager();
        $searchCriteria = $objectManager->create(SearchCriteriaBuilder::class)
            ->addFilter(ProductInterface::SKU, $skus, 'in')
            ->create();
        $resourceConnection = $objectManager->get(ResourceConnection::class);
        $profiler = $resourceConnection->getConnection()->getProfiler();
        $wasEnabled = $profiler->getEnabled();
        $profiler->clear();
        $profiler->setEnabled(true);
        try {
            $this->productRepository->cleanCache();
            foreach ($this->productRepository->getList($searchCriteria)->getItems() as $product) {
                $this->toComparableArray($product);
            }
            $queryProfiles = $profiler->getQueryProfiles(\Zend_Db_Profiler::SELECT) ?: [];
        } finally {
            $profiler->setEnabled($wasEnabled);
            $profiler->clear();
        }

        $counts = [];
        foreach (self::PRELOADED_TABLES as $table) {
            $from = 'FROM `' . $resourceConnection->getTableName($table) . '`';
            $counts[$table] = count(
                array_filter($queryProfiles, fn ($queryProfile) => str_contains($queryProfile->getQuery(), $from))
            );
        }

        return $counts;
    }

    /**
     * Convert the product the way the Web API serializes it and keep the fields that the preloader fills
     *
     * @param ProductInterface $product
     * @return array
     */
    private function toComparableArray(ProductInterface $product): array
    {
        $data = Bootstrap::getObjectManager()->get(DataObjectProcessor::class)
            ->buildOutputDataArray($product, ProductInterface::class);
        $data = array_intersect_key($data, array_flip(self::COMPARED_FIELDS));
        $data['extension_attributes'] = array_intersect_key(
            $data['extension_attributes'] ?? [],
            ['website_ids' => true]
        );

        return $data;
    }
}
