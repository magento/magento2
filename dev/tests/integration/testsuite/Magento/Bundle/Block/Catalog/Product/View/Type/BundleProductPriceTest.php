<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Block\Catalog\Product\View\Type;

use Magento\Bundle\Pricing\Price\BundleOptions;
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
 * Check bundle product prices.
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation disabled
 * @magentoAppArea frontend
 */
class BundleProductPriceTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Registry */
    private $registry;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var SerializerInterface */
    private $json;

    /** @var ExecuteInStoreContext */
    private $executeInStoreContext;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var PageFactory */
    private $pageFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->registry = $this->objectManager->get(Registry::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->executeInStoreContext = $this->objectManager->get(ExecuteInStoreContext::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->pageFactory = $this->objectManager->get(PageFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('product');

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/dynamic_bundle_product_multiselect_option.php
     *
     * @return void
     */
    public function testDynamicBundleOptionPrices(): void
    {
        $expectedData = [
            'options_prices' => [
                [
                    'oldPrice' => ['amount' => 10],
                    'basePrice' => ['amount' => 10],
                    'finalPrice' => ['amount' => 10],
                ],
                [
                    'oldPrice' => ['amount' => 20],
                    'basePrice' => ['amount' => 20],
                    'finalPrice' => ['amount' => 20],
                ],
                [
                    'oldPrice' => ['amount' => 30],
                    'basePrice' => ['amount' => 30],
                    'finalPrice' => ['amount' => 30],
                ],
            ],
            'bundle_prices' => [
                'oldPrice' => ['amount' => 0],
                'basePrice' => ['amount' => 0],
                'finalPrice' => ['amount' => 0],
            ]
        ];
        $this->processBundlePriceView('bundle_product', $expectedData);
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/dynamic_bundle_product_on_second_website.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testDynamicBundleOptionPricesOnSecondWebsite(): void
    {
        $this->executeInStoreContext->execute(
            'fixture_second_store',
            [$this, 'processBundlePriceView'],
            'dynamic_bundle_product_with_special_price',
            [
                'options_prices' => [
                    [
                        'oldPrice' => ['amount' => 20],
                        'basePrice' => ['amount' => 7.5],
                        'finalPrice' => ['amount' => 7.5],
                    ],
                    [
                        'oldPrice' => ['amount' => 40],
                        'basePrice' => ['amount' => 22.5],
                        'finalPrice' => ['amount' => 22.5],
                    ],
                ],
                'bundle_prices' => [
                    'oldPrice' => ['amount' => 0],
                    'basePrice' => ['amount' => 0],
                    'finalPrice' => ['amount' => 0],
                ]
            ]
        );
        $this->processBundlePriceView(
            'dynamic_bundle_product_with_special_price',
            [
                'options_prices' => [
                    [
                        'oldPrice' => ['amount' => 10],
                        'basePrice' => ['amount' => 7.5],
                        'finalPrice' => ['amount' => 7.5],
                    ],
                    [
                        'oldPrice' => ['amount' => 20],
                        'basePrice' => ['amount' => 15],
                        'finalPrice' => ['amount' => 15],
                    ],
                ],
                'bundle_prices' => [
                    'oldPrice' => ['amount' => 0],
                    'basePrice' => ['amount' => 0],
                    'finalPrice' => ['amount' => 0],
                ]
            ]
        );
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/product_with_multiple_options_1.php
     *
     * @return void
     */
    public function testFixedBundleOptionPrices(): void
    {
        $expectedData = [
            'options_prices' => [
                [
                    'oldPrice' => ['amount' => 2.75],
                    'basePrice' => ['amount' => 2.75],
                    'finalPrice' => ['amount' => 2.75],
                ],
                [
                    'oldPrice' => ['amount' => 6.75],
                    'basePrice' => ['amount' => 6.75],
                    'finalPrice' => ['amount' => 6.75],
                ],
            ],
            'bundle_prices' => [
                'oldPrice' => ['amount' => 12.75],
                'basePrice' => ['amount' => 10],
                'finalPrice' => ['amount' => 10],
            ]
        ];
        $this->processBundlePriceView('bundle-product', $expectedData);
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/fixed_bundle_product_on_second_website.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testFixedBundleOptionPricesOnSecondWebsite(): void
    {
        $this->executeInStoreContext->execute(
            'fixture_second_store',
            [$this, 'processBundlePriceView'],
            'fixed_bundle_product_with_special_price',
            [
                'options_prices' => [
                    [
                        'oldPrice' => ['amount' => 10],
                        'basePrice' => ['amount' => 3],
                        'finalPrice' => ['amount' => 3],
                    ],
                    [
                        'oldPrice' => ['amount' => 10],
                        'basePrice' => ['amount' => 3],
                        'finalPrice' => ['amount' => 3],
                    ],
                    [
                        'oldPrice' => ['amount' => 25],
                        'basePrice' => ['amount' => 7.5],
                        'finalPrice' => ['amount' => 7.5],
                    ],
                ],
                'bundle_prices' => [
                    'oldPrice' => ['amount' => 50],
                    'basePrice' => ['amount' => 30],
                    'finalPrice' => ['amount' => 30],
                ]
            ]
        );
        $this->processBundlePriceView(
            'fixed_bundle_product_with_special_price',
            [
                'options_prices' => [
                    [
                        'oldPrice' => ['amount' => 10],
                        'basePrice' => ['amount' => 8],
                        'finalPrice' => ['amount' => 8],
                    ],
                    [
                        'oldPrice' => ['amount' => 12.5],
                        'basePrice' => ['amount' => 10],
                        'finalPrice' => ['amount' => 10],
                    ],
                    [
                        'oldPrice' => ['amount' => 25],
                        'basePrice' => ['amount' => 20],
                        'finalPrice' => ['amount' => 20],
                    ],
                ],
                'bundle_prices' => [
                    'oldPrice' => ['amount' => 60],
                    'basePrice' => ['amount' => 50],
                    'finalPrice' => ['amount' => 50],
                ]
            ]
        );
    }

    /**
     * Asserts bundle product prices from price block.
     *
     * @param string $productSku
     * @param array $expectedData
     * @return void
     */
    public function processBundlePriceView(string $productSku, array $expectedData): void
    {
        $this->objectManager->removeSharedInstance(BundleOptions::class);
        $this->registerProduct($productSku);
        /** @var Page $page */
        $page = $this->pageFactory->create();
        $page->addHandle([
            'default',
            'catalog_product_view',
            'catalog_product_view_type_bundle',
        ]);
        $page->getLayout()->generateXml();
        $block = $page->getLayout()->getBlock('product.info.bundle.options');
        $jsonConfig = $this->json->unserialize($block->getJsonConfig());
        $this->assertEquals($expectedData['bundle_prices'], $jsonConfig['prices']);
        $this->assertOptionsConfig($expectedData['options_prices'], $jsonConfig);
    }

    /**
     * Assert options prices.
     *
     * @param array $expectedData
     * @param array $actualData
     * @return void
     */
    private function assertOptionsConfig(array $expectedData, array $actualData): void
    {
        $optionConfig = $actualData['options'] ?? null;
        $this->assertNotNull($optionConfig);
        $optionConfig = reset($optionConfig);
        foreach (array_values($optionConfig['selections']) as $key => $selection) {
            $this->assertEquals($expectedData[$key], $selection['prices']);
        }
    }

    /**
     * Register the product.
     *
     * @param string $productSku
     * @return void
     */
    private function registerProduct(string $productSku): void
    {
        $product = $this->productRepository->get($productSku, false, $this->storeManager->getStore()->getId(), true);
        $this->registry->unregister('product');
        $this->registry->register('product', $product);
    }
}
