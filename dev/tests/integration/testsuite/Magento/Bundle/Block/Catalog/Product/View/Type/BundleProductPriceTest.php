<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Block\Catalog\Product\View\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
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

    /** @var Bundle */
    private $block;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Bundle::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
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
     * @param string $productSku
     * @param array $expectedData
     * @return void
     */
    private function processBundlePriceView(string $productSku, array $expectedData): void
    {
        $this->registerProduct($productSku);
        $jsonConfig = $this->json->unserialize($this->block->getJsonConfig());
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
        $product = $this->productRepository->get($productSku);
        $this->registry->unregister('product');
        $this->registry->register('product', $product);
    }
}
