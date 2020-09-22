<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Block\Product\View\Type;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Store\ExecuteInStoreContext;
use PHPUnit\Framework\TestCase;

/**
 * Check configurable product price displaying
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 */
class ConfigurableProductPriceTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Registry */
    private $registry;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var Page */
    private $page;

    /** @var ProductCustomOptionInterface */
    private $productCustomOption;

    /** @var SerializerInterface */
    private $json;

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
        $this->registry = $this->objectManager->get(Registry::class);
        $this->page = $this->objectManager->get(PageFactory::class)->create();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->productCustomOption = $this->objectManager->get(ProductCustomOptionInterface::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->executeInStoreContext = $this->objectManager->get(ExecuteInStoreContext::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('product');
        $this->registry->unregister('current_product');

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     *
     * @return void
     */
    public function testConfigurablePrice(): void
    {
        $this->assertPrice('configurable', 10.00);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_price_on_second_website.php
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testConfigurablePriceOnSecondWebsite(): void
    {
        $this->executeInStoreContext->execute('fixture_second_store', [$this, 'assertPrice'], 'configurable', 10.00);
        $this->resetPageLayout();
        $this->assertPrice('configurable', 150.00);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable_disable_first_child.php
     *
     * @return void
     */
    public function testConfigurablePriceWithDisabledFirstChild(): void
    {
        $this->assertPrice('configurable', 20.00);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable_zero_qty_first_child.php
     *
     * @return void
     */
    public function testConfigurablePriceWithOutOfStockFirstChild(): void
    {
        $this->assertPrice('configurable', 20.00);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoDataFixture Magento/CatalogRule/_files/rule_apply_as_percentage_of_original_not_logged_user.php
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testConfigurablePriceWithCatalogRule(): void
    {
        $this->assertPrice('configurable', 9.00);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable_with_custom_option_type_text.php
     *
     * @return void
     */
    public function testConfigurablePriceWithCustomOption(): void
    {
        $product = $this->getProduct('configurable');
        $this->registerProduct($product);
        $this->preparePageLayout();
        $customOptionsBlock = $this->page->getLayout()
            ->getChildBlock('product.info.options.wrapper', 'product_options');
        $option = $product->getOptions()[0] ?? null;
        $this->assertNotNull($option);
        $this->assertJsonConfig($customOptionsBlock->getJsonConfig(), '15', (int)$option->getId());
        $optionBlock = $customOptionsBlock->getChildBlock($this->productCustomOption->getGroupByType('area'));
        $optionPrice = $optionBlock->setProduct($product)->setOption($option)->getFormattedPrice();
        $this->assertEquals('+$15.00', preg_replace('/[\n\s]/', '', strip_tags($optionPrice)));
    }

    /**
     * Register the product.
     *
     * @param ProductInterface $product
     * @return void
     */
    private function registerProduct(ProductInterface $product): void
    {
        $this->registry->unregister('product');
        $this->registry->register('product', $product);
        $this->registry->unregister('current_product');
        $this->registry->register('current_product', $product);
    }

    /**
     * Prepare configurable product page.
     *
     * @return void
     */
    private function preparePageLayout(): void
    {
        $this->page->addHandle([
            'default',
            'catalog_product_view',
            'catalog_product_view_type_configurable',
        ]);
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
     * Process view product final price block html.
     *
     * @param string $sku
     * @return string
     */
    private function processPriceView(string $sku): string
    {
        $product = $this->getProduct($sku);
        $this->registerProduct($product);
        $this->preparePageLayout();

        return $this->page->getLayout()->getBlock('product.price.final')->toHtml();
    }

    /**
     * Assert that html contain price label and expected final price amount.
     *
     * @param string $sku
     * @param float $expectedPrice
     * @return void
     */
    public function assertPrice(string $sku, float $expectedPrice): void
    {
        $priceBlockHtml = $this->processPriceView($sku);
        $regexp = '/<span class="price-label">As low as<\/span>.*';
        $regexp .= '<span.*data-price-amount="%s".*<span class="price">\$%.2f<\/span><\/span>/';
        $this->assertMatchesRegularExpression(
            sprintf($regexp, round($expectedPrice, 2), $expectedPrice),
            preg_replace('/[\n\r]/', '', $priceBlockHtml)
        );
    }

    /**
     * Assert custom option price json config.
     *
     * @param string $config
     * @param string $expectedPrice
     * @param int $optionId
     * @return void
     */
    private function assertJsonConfig(string $config, string $expectedPrice, int $optionId): void
    {
        $price = $this->json->unserialize($config)[$optionId]['prices']['finalPrice']['amount'] ?? null;
        $this->assertNotNull($price);
        $this->assertEquals($expectedPrice, $price);
    }

    /**
     * Loads product by sku.s
     *
     * @param string $sku
     * @return ProductInterface
     */
    private function getProduct(string $sku): ProductInterface
    {
        return $this->productRepository->get($sku, false, $this->storeManager->getStore()->getId(), true);
    }
}
