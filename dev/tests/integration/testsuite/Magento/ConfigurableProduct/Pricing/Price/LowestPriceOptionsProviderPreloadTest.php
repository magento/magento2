<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogInventory\Model\Configuration;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as ConfigurableAttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LowestPriceOptionsProviderPreloadTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    #[
        AppArea('frontend'),
        DbIsolation(false),
        DataFixture(ConfigurableAttributeFixture::class, as: 'attribute'),
        DataFixture(ProductFixture::class, ['price' => 20], 'first_child_a'),
        DataFixture(ProductFixture::class, ['price' => 10], 'first_child_b'),
        DataFixture(ProductFixture::class, ['price' => 15], 'second_child_a'),
        DataFixture(ProductFixture::class, ['price' => 30], 'second_child_b'),
        DataFixture(ProductFixture::class, ['price' => 40], 'third_child_a'),
        DataFixture(ProductFixture::class, ['price' => 25], 'third_child_b'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attribute$'], '_links' => ['$first_child_a$', '$first_child_b$']],
            'first_configurable'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attribute$'], '_links' => ['$second_child_a$', '$second_child_b$']],
            'second_configurable'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attribute$'], '_links' => ['$third_child_a$', '$third_child_b$']],
            'third_configurable'
        ),
    ]
    public function testListingLooksUpLowestPriceChildrenWithOneQuery(): void
    {
        CacheCleaner::clean(['block_html']);
        $fixtures = DataFixtureStorageManager::getStorage();
        $collection = $this->objectManager->create(Collection::class);
        $collection->addIdFilter([
            $fixtures->get('first_configurable')->getId(),
            $fixtures->get('second_configurable')->getId(),
            $fixtures->get('third_configurable')->getId(),
        ]);
        $collection->addAttributeToSelect('*')->load();
        $block = $this->getListProductBlock($collection);

        $profiler = $this->objectManager->get(ResourceConnection::class)->getConnection()->getProfiler();
        $profiler->clear();
        $profiler->setEnabled(true);
        $html = '';
        foreach ($block->getLoadedProductCollection() as $product) {
            $html .= $block->getProductPrice($product);
        }
        $lookups = 0;
        foreach ($profiler->getQueryProfiles() ?: [] as $profile) {
            $query = $profile->getQuery();
            if (str_starts_with($query, '(') && str_contains($query, 'catalog_product_relation')) {
                $lookups++;
            }
        }
        $profiler->setEnabled(false);

        $this->assertSame(1, $lookups);
        foreach (['10.00', '15.00', '25.00'] as $price) {
            $this->assertStringContainsString('$' . $price, $html);
        }
    }

    #[
        AppArea('frontend'),
        DbIsolation(false),
        Config(Configuration::XML_PATH_SHOW_OUT_OF_STOCK, 0),
        DataFixture(ConfigurableAttributeFixture::class, as: 'attribute'),
        DataFixture(
            ProductFixture::class,
            ['price' => 5, 'extension_attributes' => ['stock_item' => ['is_in_stock' => false, 'qty' => 0]]],
            'first_child_a'
        ),
        DataFixture(ProductFixture::class, ['price' => 20], 'first_child_b'),
        DataFixture(ProductFixture::class, ['price' => 15], 'second_child_a'),
        DataFixture(ProductFixture::class, ['price' => 30], 'second_child_b'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attribute$'], '_links' => ['$first_child_a$', '$first_child_b$']],
            'first_configurable'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attribute$'], '_links' => ['$second_child_a$', '$second_child_b$']],
            'second_configurable'
        ),
    ]
    public function testPreloadMatchesPerProductResultWithOutOfStockChildHidden(): void
    {
        $this->assertPreloadMatchesPerProductResult(['first_configurable', 'second_configurable']);
    }

    #[
        AppArea('frontend'),
        DbIsolation(false),
        Config(Configuration::XML_PATH_SHOW_OUT_OF_STOCK, 1),
        DataFixture(ConfigurableAttributeFixture::class, as: 'attribute'),
        DataFixture(
            ProductFixture::class,
            ['price' => 5, 'extension_attributes' => ['stock_item' => ['is_in_stock' => false, 'qty' => 0]]],
            'first_child_a'
        ),
        DataFixture(ProductFixture::class, ['price' => 20], 'first_child_b'),
        DataFixture(ProductFixture::class, ['price' => 15], 'second_child_a'),
        DataFixture(ProductFixture::class, ['price' => 30], 'second_child_b'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attribute$'], '_links' => ['$first_child_a$', '$first_child_b$']],
            'first_configurable'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attribute$'], '_links' => ['$second_child_a$', '$second_child_b$']],
            'second_configurable'
        ),
    ]
    public function testPreloadMatchesPerProductResultWithOutOfStockChildShown(): void
    {
        $this->assertPreloadMatchesPerProductResult(['first_configurable', 'second_configurable']);
    }

    #[
        AppArea('frontend'),
        DbIsolation(false),
        DataFixture(ConfigurableAttributeFixture::class, as: 'attribute'),
        DataFixture(ProductFixture::class, ['price' => 10], 'shared_child'),
        DataFixture(ProductFixture::class, ['price' => 20], 'first_child'),
        DataFixture(ProductFixture::class, ['price' => 30], 'second_child'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attribute$'], '_links' => ['$shared_child$', '$first_child$']],
            'first_configurable'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attribute$'], '_links' => ['$shared_child$', '$second_child$']],
            'second_configurable'
        ),
    ]
    public function testPreloadMatchesPerProductResultForSharedChild(): void
    {
        $sharedChildId = (int)DataFixtureStorageManager::getStorage()->get('shared_child')->getId();
        $actual = $this->assertPreloadMatchesPerProductResult(['first_configurable', 'second_configurable']);

        $this->assertSame([[$sharedChildId], [$sharedChildId]], $actual);
    }

    /**
     * @param string[] $fixtureNames
     * @return array
     */
    private function assertPreloadMatchesPerProductResult(array $fixtureNames): array
    {
        $parents = array_map([$this, 'loadProduct'], $fixtureNames);
        $expected = [];
        foreach ($parents as $parent) {
            $expected[] = array_keys(
                $this->objectManager->create(LowestPriceOptionsProvider::class)->getProducts($parent)
            );
        }

        $provider = $this->objectManager->create(LowestPriceOptionsProvider::class);
        $provider->preloadProducts($parents);
        $actual = [array_keys($provider->getProducts($parents[0]))];
        $profiler = $this->objectManager->get(ResourceConnection::class)->getConnection()->getProfiler();
        $profiler->clear();
        $profiler->setEnabled(true);
        foreach (array_slice($parents, 1) as $parent) {
            $actual[] = array_keys($provider->getProducts($parent));
        }
        $queries = $profiler->getTotalNumQueries();
        $profiler->setEnabled(false);

        $this->assertSame($expected, $actual);
        $this->assertSame(0, $queries);

        return $actual;
    }

    private function loadProduct(string $fixtureName): ProductInterface
    {
        return $this->objectManager->get(ProductRepositoryInterface::class)->get(
            DataFixtureStorageManager::getStorage()->get($fixtureName)->getSku(),
            false,
            1,
            true
        );
    }

    private function getListProductBlock(Collection $collection): ListProduct
    {
        $page = $this->objectManager->get(PageFactory::class)->create();
        $page->addHandle(['default', 'catalog_category_view']);
        $page->getLayout()->generateXml();
        $block = $page->getLayout()->getBlock('category.products')->getChildBlock('product_list');
        $block->setCollection($collection);

        return $block;
    }
}
