<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Block\Product\View\Type;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Group;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Result\Page;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Check configurable product price displaying
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableProductPriceTest extends TestCase
{
    private const FINAL_PRICE_BLOCK_NAME = 'product.price.final';

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Registry */
    private $registry;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var Page */
    private $page;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var ProductCustomOptionInterface */
    private $productCustomOption;

    /** @var SerializerInterface */
    private $json;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->get(Registry::class);
        $this->page = $this->objectManager->get(Page::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->productCustomOption = $this->objectManager->get(ProductCustomOptionInterface::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
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
        $priceBlockHtml = $this->processPriceView('configurable');
        $this->assertPrice(preg_replace('/[\n\r]/', '', $priceBlockHtml), 10.00);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable_disable_first_child.php
     *
     * @return void
     */
    public function testConfigurablePriceWithDisabledFirstChild(): void
    {
        $priceBlockHtml = $this->processPriceView('configurable');
        $this->assertPrice(preg_replace('/[\n\r]/', '', $priceBlockHtml), 20.00);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable_zero_qty_first_child.php
     *
     * @return void
     */
    public function testConfigurablePriceWithOutOfStockFirstChild(): void
    {
        $priceBlockHtml = $this->processPriceView('configurable');
        $this->assertPrice(preg_replace('/[\n\r]/', '', $priceBlockHtml), 20.00);
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
        $priceBlockHtml = $this->processPriceView('configurable');
        $this->assertPrice(preg_replace('/[\n\r]/', '', $priceBlockHtml), 9.00);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable_with_custom_option_type_text.php
     *
     * @return void
     */
    public function testConfigurablePriceWithCustomOption(): void
    {
        $product = $this->productRepository->get('configurable');
        $this->registerProduct($product);
        $this->preparePageLayout();
        $customOptionsBlock = $this->getProductOptionsBlock('product_options');
        $option = $product->getOptions()[0] ?? null;
        $this->assertNotNull($option);
        $this->assertJsonConfig($customOptionsBlock->getJsonConfig(), '15', (int)$option->getId());
        $optionBlock = $customOptionsBlock->getChildBlock($this->productCustomOption->getGroupByType('area'));
        $optionPrice = $optionBlock->setProduct($product)->setOption($option)->getFormattedPrice();
        $this->assertEquals('+$15.00', preg_replace('/[\n\s]/', '', strip_tags($optionPrice)));
    }

    /**
     * @dataProvider childProductsDataProvider
     * @magentoDataFixture Magento/Swatches/_files/configurable_product_visual_swatch_attribute.php
     * @magentoCache config disabled
     *
     * @param array $updateData
     * @param array $expectedData
     * @return void
     */
    public function testConfigurableOptionPrices(array $updateData, array $expectedData): void
    {
        $this->updateProducts($updateData);
        $product = $this->productRepository->get('configurable');
        $this->registerProduct($product);
        $this->preparePageLayout();
        $configurableOptions = $this->getProductOptionsBlock('swatch_options')->getJsonConfig();
        $optionPrices = $this->json->unserialize($configurableOptions)['optionPrices'];
        $this->assertEquals($expectedData, array_values($optionPrices));
    }

    /**
     * @return array
     */
    public function childProductsDataProvider(): array
    {
        return [
            [
                'update_data' => [
                    'simple_option_1' => [
                        'special_price' => 50,
                    ],
                    'simple_option_2' => [
                        'special_price' => 58.55,
                    ],
                    'simple_option_3' => [
                        'tier_price' => [
                            [
                                'website_id' => 0,
                                'cust_group' => Group::CUST_GROUP_ALL,
                                'price_qty' => 1,
                                'value_type' => TierPriceInterface::PRICE_TYPE_FIXED,
                                'price' => 75,
                            ],
                        ],
                    ],
                ],
                'expected_data' => [
                    [
                        'oldPrice' => ['amount' => 150],
                        'basePrice' => ['amount' => 50],
                        'finalPrice' => ['amount' => 50],
                        'tierPrices' => [],
                        'msrpPrice' => ['amount' => null],
                    ],
                    [
                        'oldPrice' => ['amount' => 150],
                        'basePrice' => ['amount' => 58.55],
                        'finalPrice' => ['amount' => 58.55],
                        'tierPrices' => [],
                        'msrpPrice' => ['amount' => null],
                    ],
                    [
                        'oldPrice' => ['amount' => 150],
                        'basePrice' => ['amount' => 75],
                        'finalPrice' => ['amount' => 75],
                        'tierPrices' => [],
                        'msrpPrice' => ['amount' => null],
                    ],
                ]
            ],
        ];
    }

    /**
     * Update products.
     *
     * @param array $data
     * @return void
     */
    private function updateProducts(array $data): void
    {
        $currentStore = $this->storeManager->getStore();
        try {
            $this->storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);
            foreach ($data as $sku => $updateData) {
                $product = $this->productRepository->get($sku);
                $product->addData($updateData);
                $this->productRepository->save($product);
            }
        } finally {
            $this->storeManager->setCurrentStore($currentStore);
        }
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
     * Process view product final price block html.
     *
     * @param string $sku
     * @return string
     */
    private function processPriceView(string $sku): string
    {
        $product = $this->productRepository->get($sku);
        $this->registerProduct($product);
        $this->preparePageLayout();

        return $this->page->getLayout()->getBlock(self::FINAL_PRICE_BLOCK_NAME)->toHtml();
    }

    /**
     * Get product options block.
     *
     * @param string
     * @return AbstractBlock
     */
    private function getProductOptionsBlock(string $nameOptionBlock): AbstractBlock
    {
        /** @var Template $productInfoFormOptionsBlock */
        $productInfoFormOptionsBlock = $this->page->getLayout()->getBlock('product.info.form.options');
        $productOptionsWrapperBlock = $productInfoFormOptionsBlock->getChildBlock('product_options_wrapper');

        return $productOptionsWrapperBlock->getChildBlock($nameOptionBlock);
    }

    /**
     * Assert that html contain price label and expected final price amount.
     *
     * @param string $priceBlockHtml
     * @param float $expectedPrice
     * @return void
     */
    private function assertPrice(string $priceBlockHtml, float $expectedPrice): void
    {
        $regexp = '/<span class="price-label">As low as<\/span>.*';
        $regexp .= '<span.*data-price-amount="%s".*<span class="price">\$%.2f<\/span><\/span>/';
        $this->assertRegExp(sprintf($regexp, round($expectedPrice, 2), $expectedPrice), $priceBlockHtml);
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
}
