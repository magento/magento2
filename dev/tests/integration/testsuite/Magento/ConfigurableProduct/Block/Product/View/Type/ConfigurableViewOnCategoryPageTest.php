<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Block\Product\View\Type;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Test\Fixture\AssignProducts as AssignProductsFixture;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Store\ExecuteInStoreContext;
use PHPUnit\Framework\TestCase;

/**
 * Class checks configurable product displaying on category view page
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_out_of_stock_children.php
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableViewOnCategoryPageTest extends TestCase
{
    /** @var ObjectManagerInterface  */
    private $objectManager;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var Page */
    private $page;

    /** @var Registry */
    private $registry;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var ExecuteInStoreContext */
    private $executeInStoreContext;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
        $this->page = $this->objectManager->get(PageFactory::class)->create();
        $this->registry = $this->objectManager->get(Registry::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->executeInStoreContext = $this->objectManager->get(ExecuteInStoreContext::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('current_category');
        parent::tearDown();
    }

    /**
     * @magentoConfigFixture current_store cataloginventory/options/show_out_of_stock 1
     *
     * @return void
     */
    public function testOutOfStockProductWithEnabledConfigView(): void
    {
        $this->preparePageLayout();
        $this->assertCollectionSize(1, $this->getListingBlock()->getLoadedProductCollection());
    }

    /**
     * @magentoConfigFixture current_store cataloginventory/options/show_out_of_stock 0
     *
     * @return void
     */
    public function testOutOfStockProductWithDisabledConfigView(): void
    {
        $this->preparePageLayout();
        $this->assertCollectionSize(0, $this->getListingBlock()->getLoadedProductCollection());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/reindex_catalog_inventory_stock.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_category.php
     *
     * @return void
     */
    public function testCheckConfigurablePrice(): void
    {
        $this->assertProductPrice('configurable', 'As low as $10.00');
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_price_on_second_website.php
     *
     * @return void
     */
    public function testCheckConfigurablePriceOnSecondWebsite(): void
    {
        $this->executeInStoreContext->execute(
            'fixture_second_store',
            [$this, 'assertProductPriceContains'],
            'configurable',
            __('As low as') . ' $10.00'
        );
        $this->resetPageLayout();
        $this->assertProductPrice('configurable', '$150.00');
    }

    #[
        AppIsolation(true),
        DataFixture(CategoryFixture::class, [], 'category'),
        DataFixture(ProductFixture::class, [
            'price' => 10.0,
            'visibility' => Visibility::VISIBILITY_NOT_VISIBLE
        ], 'p1'),
        DataFixture(AttributeFixture::class, as: 'attr'),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                '_options' => ['$attr$'],
                '_links' => ['$p1$']
            ],
            'configurable'
        ),
        DataFixture(
            AssignProductsFixture::class,
            ['products' => ['$configurable$', '$p1$'], 'category' => '$category$'],
            as: 'assignProducts'
        )
    ]
    public function testCheckConfigurablePriceOnOneSimple(): void
    {
        $this->resetPageLayout();
        $fixtures = DataFixtureStorageManager::getStorage();
        $configurableSku = $fixtures->get('configurable')->getSku();

        $this->registry->unregister('current_category');
        $this->registry->register(
            'current_category',
            $fixtures->get('category')
        );
        $this->page->addHandle(['default', 'catalog_category_view']);
        $this->page->getLayout()->generateXml();

        $this->assertCollectionSize(1, $this->getListingBlock()->getLoadedProductCollection());
        $priceHtml = $this->getListingBlock()->getProductPrice($this->getProduct($configurableSku));
        $this->assertStringContainsString('$10.00', $this->clearPriceHtml($priceHtml));
        $this->assertStringNotContainsString('As low as', $this->clearPriceHtml($priceHtml));
    }

    #[
        AppIsolation(true),
        DataFixture(CategoryFixture::class, [], 'category'),
        DataFixture(ProductFixture::class, [
            'price' => 10.0,
            'visibility' => Visibility::VISIBILITY_NOT_VISIBLE
        ], 'p1'),
        DataFixture(ProductFixture::class, [
            'price' => 12.0,
            'visibility' => Visibility::VISIBILITY_NOT_VISIBLE
        ], 'p2'),
        DataFixture(AttributeFixture::class, as: 'attr'),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                '_options' => ['$attr$'],
                '_links' => ['$p1$', '$p2$']
            ],
            'configurable'
        ),
        DataFixture(
            AssignProductsFixture::class,
            ['products' => ['$configurable$', '$p1$', '$p2$'], 'category' => '$category$'],
            as: 'assignProducts'
        )
    ]
    public function testCheckConfigurablePriceOnTwoSimple(): void
    {
        $this->resetPageLayout();
        $fixtures = DataFixtureStorageManager::getStorage();
        $configurableSku = $fixtures->get('configurable')->getSku();

        $this->registry->unregister('current_category');
        $this->registry->register(
            'current_category',
            $fixtures->get('category')
        );
        $this->page->addHandle(['default', 'catalog_category_view']);
        $this->page->getLayout()->generateXml();

        $this->assertCollectionSize(1, $this->getListingBlock()->getLoadedProductCollection());
        $priceHtml = $this->getListingBlock()->getProductPrice($this->getProduct($configurableSku));
        $this->assertStringContainsString('$10.00', $this->clearPriceHtml($priceHtml));
        $this->assertStringContainsString('As low as', $this->clearPriceHtml($priceHtml));
    }

    /**
     * @param string $sku
     * @param string $priceString
     * @return void
     */
    public function assertProductPriceContains(string $sku, string $priceString): void
    {
        $this->preparePageLayout();
        $this->assertCollectionSize(1, $this->getListingBlock()->getLoadedProductCollection());
        $priceHtml = $this->getListingBlock()->getProductPrice($this->getProduct($sku));
        $this->assertStringContainsString($priceString, $this->clearPriceHtml($priceHtml));
    }

    /**
     * Checks product price.
     *
     * @param string $sku
     * @param string $priceString
     * @return void
     */
    public function assertProductPrice(string $sku, string $priceString): void
    {
        $this->preparePageLayout();
        $this->assertCollectionSize(1, $this->getListingBlock()->getLoadedProductCollection());
        $priceHtml = $this->getListingBlock()->getProductPrice($this->getProduct($sku));
        $this->assertEquals($priceString, $this->clearPriceHtml($priceHtml));
    }

    /**
     * Check collection size
     *
     * @param int $expectedSize
     * @param AbstractCollection $collection
     * @return void
     */
    private function assertCollectionSize(int $expectedSize, AbstractCollection $collection): void
    {
        $this->assertEquals($expectedSize, $collection->getSize());
        $this->assertCount($expectedSize, $collection->getItems());
    }

    /**
     * Prepare category page.
     *
     * @return void
     */
    private function preparePageLayout(): void
    {
        $this->registry->unregister('current_category');
        $this->registry->register(
            'current_category',
            $this->categoryRepository->get(333, $this->storeManager->getStore()->getId())
        );
        $this->page->addHandle(['default', 'catalog_category_view']);
        $this->page->getLayout()->generateXml();
    }

    /**
     * Reset layout page to get new block html.
     *
     * @return void
     */
    private function resetPageLayout(): void
    {
        $this->page = $this->objectManager->get(PageFactory::class)->create();
    }

    /**
     * Removes html tags and spaces from price html string.
     *
     * @param string $priceHtml
     * @return string
     */
    private function clearPriceHtml(string $priceHtml): string
    {
        return trim(preg_replace('/\s+/', ' ', strip_tags($priceHtml)));
    }

    /**
     * Returns product list block.
     *
     * @return null|ListProduct
     */
    private function getListingBlock(): ?ListProduct
    {
        $block = $this->page->getLayout()->getBlock('category.products.list');

        return $block ? $block : null;
    }

    /**
     * Loads product by sku.
     *
     * @param string $sku
     * @return ProductInterface
     */
    private function getProduct(string $sku): ProductInterface
    {
        return $this->productRepository->get($sku, false, $this->storeManager->getStore()->getId(), true);
    }
}
